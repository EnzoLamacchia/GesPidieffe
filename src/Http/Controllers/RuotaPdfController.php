<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Elamacchia\Gespidieffe\Services\ContatorePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RuotaPdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::ruota.upload');
    }

    // ─────────────────────────────────────────────────────────────
    // Step 1b – Upload (POST: riceve il file)
    // ─────────────────────────────────────────────────────────────

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:102400'],
        ]);

        $uuid = Str::uuid()->toString();
        $base = storage_path('app/' . $this->folder . '/');

        $filename = $uuid . '.pdf';
        Storage::disk($this->disk)->putFileAs($this->folder, $request->file('pdf'), $filename);

        $pages    = $this->qpdfPageCount($base . $filename);
        $original = $request->file('pdf')->getClientOriginalName();

        return redirect()->route('gespidieffe.ruota.editor', ['file' => $uuid])
                         ->with('ruota_meta', compact('pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor
    // ─────────────────────────────────────────────────────────────

    public function editor(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $pages    = $this->qpdfPageCount($path);
        $original = session('ruota_meta.original', basename($path));

        return view('gespidieffe::ruota.editor', compact('file', 'pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve il PDF grezzo al browser (usato da PDF.js)
    // ─────────────────────────────────────────────────────────────

    public function servePdf(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Applica rotazioni
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'file'     => ['required', 'uuid'],
            'rotazioni' => ['required', 'string', 'max:20000'],
        ]);

        $uuid = $request->input('file');
        $base = storage_path('app/' . $this->folder . '/');
        $src  = $base . $uuid . '.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $totPages = $this->qpdfPageCount($src);
        abort_if($totPages === 0, 422, 'Impossibile leggere le pagine del PDF.');

        // Parsing JSON: array di oggetti [{page: N, rotation: 90|180|270}]
        $rotazioni = json_decode($request->input('rotazioni'), true);
        abort_if(!is_array($rotazioni), 422, 'Formato rotazioni non valido.');

        // Filtra solo le pagine con rotazione != 0
        $daRuotare = array_filter($rotazioni, fn($r) => isset($r['page'], $r['rotation']) && $r['rotation'] !== 0);

        // Se nessuna rotazione, copia il file direttamente
        $outName = $uuid . '_ruotato.pdf';
        $outPath = $base . $outName;

        if (empty($daRuotare)) {
            copy($src, $outPath);
        } else {
            // Costruisce i flag --rotate per qpdf
            $flags = '';
            foreach ($daRuotare as $r) {
                $page = (int) $r['page'];
                $deg  = (int) $r['rotation'];

                // Valori ammessi: +90, +180, +270
                if (!in_array($deg, [90, 180, 270], true)) continue;
                if ($page < 1 || $page > $totPages) continue;

                $flags .= sprintf(' --rotate=+%d:%d', $deg, $page);
            }

            $cmd = sprintf(
                'qpdf %s%s -- %s 2>/dev/null',
                escapeshellarg($src),
                $flags,
                escapeshellarg($outPath)
            );
            exec($cmd);

            abort_unless(file_exists($outPath), 500, 'Elaborazione fallita.');
        }

        (new ContatorePdfService())->incrementa('ruota');

        return response()->json([
            'download_token' => $uuid . '_ruotato',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download PDF risultante
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'ruotato.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    // ─────────────────────────────────────────────────────────────
    // Elimina file temporanei
    // ─────────────────────────────────────────────────────────────

    public function elimina(string $file)
    {
        $base = storage_path('app/' . $this->folder . '/');

        foreach (glob($base . $file . '*') as $f) {
            @unlink($f);
        }

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conta pagine con qpdf
    // ─────────────────────────────────────────────────────────────

    private function qpdfPageCount(string $pdfPath): int
    {
        $out = shell_exec(sprintf('qpdf --show-npages %s 2>/dev/null', escapeshellarg($pdfPath)));

        return (int) trim($out ?? '0');
    }
}