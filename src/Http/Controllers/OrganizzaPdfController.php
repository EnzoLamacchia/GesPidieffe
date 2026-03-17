<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizzaPdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::organizza.upload');
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

        return redirect()->route('gespidieffe.organizza.editor', ['file' => $uuid])
                         ->with('organizza_meta', compact('pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor (griglia miniature drag&drop)
    // ─────────────────────────────────────────────────────────────

    public function editor(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $pages    = $this->qpdfPageCount($path);
        $original = session('organizza_meta.original', basename($path));

        return view('gespidieffe::organizza.editor', compact('file', 'pages', 'original'));
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
    // Step 3 – Applica organizzazione (POST con ordine pagine)
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'file'   => ['required', 'uuid'],
            'ordine' => ['required', 'string', 'max:10000'],
        ]);

        $uuid  = $request->input('file');
        $base  = storage_path('app/' . $this->folder . '/');
        $src   = $base . $uuid . '.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $totPages = $this->qpdfPageCount($src);
        abort_if($totPages === 0, 422, 'Impossibile leggere le pagine del PDF.');

        // Parsing ordine: array di numeri di pagina (1-based), es. [3,1,2,1,4]
        $ordine = array_map('intval', explode(',', $request->input('ordine')));

        // Validazione: ogni numero deve essere tra 1 e totPages
        foreach ($ordine as $n) {
            abort_if($n < 1 || $n > $totPages, 422, "Numero di pagina non valido: {$n}.");
        }

        abort_if(empty($ordine), 422, 'Nessuna pagina specificata.');

        // Costruisce la stringa pagine per qpdf (es. "3,1,2,1,4")
        $paginaStr = implode(',', $ordine);
        $outName   = $uuid . '_organizzato.pdf';
        $outPath   = $base . $outName;

        $cmd = sprintf(
            'qpdf %s --pages . %s -- %s 2>/dev/null',
            escapeshellarg($src),
            escapeshellarg($paginaStr),
            escapeshellarg($outPath)
        );
        exec($cmd);

        abort_unless(file_exists($outPath), 500, 'Elaborazione fallita.');

        return response()->json([
            'download_token' => $uuid . '_organizzato',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download PDF risultante
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'organizzato.pdf', [
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
