<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class SplitPdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::split.upload');
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

        return redirect()->route('gespidieffe.split.editor', ['file' => $uuid])
                         ->with('split_meta', compact('pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor (selezione modalità e intervalli)
    // ─────────────────────────────────────────────────────────────

    public function editor(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $pages    = $this->qpdfPageCount($path);
        $original = session('split_meta.original', basename($path));

        return view('gespidieffe::split.editor', compact('file', 'pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve una singola pagina come PNG (anteprima PDF.js)
    // ─────────────────────────────────────────────────────────────

    public function servePdf(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Applica split (POST con modalità + intervalli)
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'file'      => ['required', 'uuid'],
            'modalita'  => ['required', 'in:singole,intervalli'],
            'intervalli' => ['nullable', 'string', 'max:2000'],
        ]);

        $uuid  = $request->input('file');
        $base  = storage_path('app/' . $this->folder . '/');
        $src   = $base . $uuid . '.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $totPages = $this->qpdfPageCount($src);
        abort_if($totPages === 0, 422, 'Impossibile leggere le pagine del PDF.');

        // Costruisce la lista di range da estrarre
        if ($request->input('modalita') === 'singole') {
            // Una range per ogni pagina
            $ranges = array_map(fn($n) => [$n, $n], range(1, $totPages));
        } else {
            // Parsing della stringa intervalli, es. "1-3, 5, 7-9"
            $ranges = $this->parseIntervalli($request->input('intervalli', ''), $totPages);
            abort_if(empty($ranges), 422, 'Nessun intervallo valido specificato.');
        }

        // Genera i file split
        $outputFiles = [];
        foreach ($ranges as $i => [$from, $to]) {
            $label   = $from === $to ? "p{$from}" : "p{$from}-{$to}";
            $outName = $uuid . '_split_' . $i . '_' . $label . '.pdf';
            $outPath = $base . $outName;

            $cmd = sprintf(
                'qpdf %s --pages . %s -- %s 2>/dev/null',
                escapeshellarg($src),
                escapeshellarg("{$from}-{$to}"),
                escapeshellarg($outPath)
            );
            exec($cmd);

            abort_unless(file_exists($outPath), 500, "Split fallito per il range {$from}-{$to}.");

            $outputFiles[] = [
                'path'  => $outPath,
                'name'  => $label . '.pdf',
                'label' => $label,
            ];
        }

        // Se un solo file non comprime, scarica direttamente
        if (count($outputFiles) === 1) {
            $token = $uuid . '_split_0_' . $outputFiles[0]['label'];

            return response()->json([
                'tipo'           => 'singolo',
                'download_token' => $token,
                'filename'       => $outputFiles[0]['name'],
            ]);
        }

        // Più file → crea ZIP
        $zipName = $uuid . '_split.zip';
        $zipPath = $base . $zipName;

        $zip = new ZipArchive();
        abort_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Impossibile creare lo ZIP.');
        foreach ($outputFiles as $f) {
            $zip->addFile($f['path'], $f['name']);
        }
        $zip->close();

        abort_unless(file_exists($zipPath), 500, 'ZIP non creato.');

        return response()->json([
            'tipo'           => 'zip',
            'download_token' => $uuid . '_split',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download PDF singolo
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        // Ricava il nome leggibile dal token (es. uuid_split_0_p1-3.pdf)
        $parts    = explode('_split_', $file);
        $label    = count($parts) >= 2 ? last(explode('_', $file)) : 'parte';
        $filename = 'split_' . $label . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    // ─────────────────────────────────────────────────────────────
    // Download ZIP
    // ─────────────────────────────────────────────────────────────

    public function downloadZip(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.zip');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'split.zip', [
            'Content-Type' => 'application/zip',
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

    // ─────────────────────────────────────────────────────────────
    // Helper – parsing intervalli "1-3, 5, 7-9"
    // ─────────────────────────────────────────────────────────────

    private function parseIntervalli(string $raw, int $maxPages): array
    {
        $ranges = [];
        $tokens = preg_split('/[\s,;]+/', trim($raw));

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') continue;

            if (preg_match('/^(\d+)-(\d+)$/', $token, $m)) {
                $from = (int) $m[1];
                $to   = (int) $m[2];
            } elseif (preg_match('/^\d+$/', $token)) {
                $from = $to = (int) $token;
            } else {
                continue; // token non valido, ignorato
            }

            if ($from < 1 || $to < $from || $to > $maxPages) continue;

            $ranges[] = [$from, $to];
        }

        return $ranges;
    }
}