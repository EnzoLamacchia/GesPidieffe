<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MergePdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::merge.upload');
    }

    // ─────────────────────────────────────────────────────────────
    // Step 1b – Upload (POST: riceve i file, crea sessione)
    // ─────────────────────────────────────────────────────────────

    public function upload(Request $request)
    {
        $request->validate([
            'pdfs'   => ['required', 'array', 'min:2', 'max:20'],
            'pdfs.*' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $session = Str::uuid()->toString();
        $base    = storage_path('app/' . $this->folder . '/');

        $files = [];
        foreach ($request->file('pdfs') as $i => $file) {
            $filename = $session . '_f' . $i . '.pdf';
            Storage::disk($this->disk)->putFileAs($this->folder, $file, $filename);
            $files[] = [
                'index'    => $i,
                'filename' => $filename,
                'original' => $file->getClientOriginalName(),
                'pages'    => $this->qpdfPageCount($base . $filename),
            ];
        }

        // Salva il manifest della sessione in JSON
        $manifest = [
            'session' => $session,
            'files'   => $files,
            'order'   => array_column($files, 'index'),
        ];
        Storage::disk($this->disk)->put(
            $this->folder . '/' . $session . '_manifest.json',
            json_encode($manifest)
        );

        return redirect()->route('gespidieffe.merge.editor', ['session' => $session]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor (riordinamento file)
    // ─────────────────────────────────────────────────────────────

    public function editor(string $session)
    {
        $manifestPath = storage_path('app/' . $this->folder . '/' . $session . '_manifest.json');
        abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return view('gespidieffe::merge.editor', [
            'session'  => $session,
            'manifest' => $manifest,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve il PDF grezzo di un singolo file al browser (PDF.js)
    // ─────────────────────────────────────────────────────────────

    public function servePdf(string $session, int $index)
    {
        $base = storage_path('app/' . $this->folder . '/');
        $path = $base . $session . '_f' . $index . '.pdf';
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Applica merge (POST con ordine finale)
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'session' => ['required', 'uuid'],
            'order'   => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'min:0'],
        ]);

        $session      = $request->input('session');
        $order        = $request->input('order');
        $base         = storage_path('app/' . $this->folder . '/');
        $manifestPath = $base . $session . '_manifest.json';

        abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');
        $manifest = json_decode(file_get_contents($manifestPath), true);

        // Costruisce la lista di file nell'ordine richiesto
        $filesByIndex = [];
        foreach ($manifest['files'] as $f) {
            $filesByIndex[$f['index']] = $base . $f['filename'];
        }

        $orderedPaths = [];
        foreach ($order as $idx) {
            abort_unless(isset($filesByIndex[$idx]), 422, "File con indice $idx non trovato.");
            $orderedPaths[] = $filesByIndex[$idx];
        }

        // Verifica che tutti i file esistano
        foreach ($orderedPaths as $path) {
            abort_unless(file_exists($path), 404, 'File PDF non trovato: ' . basename($path));
        }

        // Merge con qpdf
        $outToken = $session . '_merged';
        $outPath  = $base . $outToken . '.pdf';

        $pageArgs = implode(' ', array_map(fn($f) => escapeshellarg($f), $orderedPaths));
        $cmd = sprintf(
            'qpdf --empty --pages %s -- %s 2>/dev/null',
            $pageArgs,
            escapeshellarg($outPath)
        );
        exec($cmd);

        abort_unless(file_exists($outPath), 500, 'Merge fallito.');

        return response()->json(['download_token' => $outToken]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'documento_unito.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    // ─────────────────────────────────────────────────────────────
    // Elimina file temporanei della sessione
    // ─────────────────────────────────────────────────────────────

    public function elimina(string $session)
    {
        $base = storage_path('app/' . $this->folder . '/');

        foreach (glob($base . $session . '*') as $f) {
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
