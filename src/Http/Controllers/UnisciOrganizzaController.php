<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Elamacchia\Gespidieffe\Services\ContatorePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnisciOrganizzaController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::unisciorganizza.upload');
    }

    // ─────────────────────────────────────────────────────────────
    // Step 1b – Upload POST: riceve i file, crea sessione o aggiunge
    // ─────────────────────────────────────────────────────────────

    public function upload(Request $request)
    {
        $existingSession = $request->input('existing_session');

        if ($existingSession) {
            // ── Caso A: aggiunta a sessione esistente (min 1 file) ────────────────
            $request->validate([
                'pdfs'   => ['required', 'array', 'min:1', 'max:20'],
                'pdfs.*' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            ]);

            $manifestPath = storage_path('app/' . $this->folder . '/' . $existingSession . '_uo_manifest.json');
            abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');

            $manifest  = json_decode(file_get_contents($manifestPath), true);
            $base      = storage_path('app/' . $this->folder . '/');
            $nextIndex = empty($manifest['files']) ? 0 : (max(array_column($manifest['files'], 'index')) + 1);

            foreach ($request->file('pdfs') as $i => $file) {
                $idx      = $nextIndex + $i;
                $filename = $existingSession . '_uo_f' . $idx . '.pdf';
                Storage::disk($this->disk)->putFileAs($this->folder, $file, $filename);
                $manifest['files'][] = [
                    'index'    => $idx,
                    'filename' => $filename,
                    'original' => $file->getClientOriginalName(),
                    'pages'    => $this->qpdfPageCount($base . $filename),
                ];
                $manifest['order'][] = $idx;
            }

            Storage::disk($this->disk)->put(
                $this->folder . '/' . $existingSession . '_uo_manifest.json',
                json_encode($manifest)
            );

            return redirect()->route('gespidieffe.unisciorganizza.editor-merge', ['session' => $existingSession]);
        }

        // ── Caso B: nuova sessione (min 2 file) ───────────────────────────────
        $request->validate([
            'pdfs'   => ['required', 'array', 'min:2', 'max:20'],
            'pdfs.*' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $session = Str::uuid()->toString();
        $base    = storage_path('app/' . $this->folder . '/');

        $files = [];
        foreach ($request->file('pdfs') as $i => $file) {
            $filename = $session . '_uo_f' . $i . '.pdf';
            Storage::disk($this->disk)->putFileAs($this->folder, $file, $filename);
            $files[] = [
                'index'    => $i,
                'filename' => $filename,
                'original' => $file->getClientOriginalName(),
                'pages'    => $this->qpdfPageCount($base . $filename),
            ];
        }

        $manifest = [
            'session' => $session,
            'files'   => $files,
            'order'   => array_column($files, 'index'),
        ];
        Storage::disk($this->disk)->put(
            $this->folder . '/' . $session . '_uo_manifest.json',
            json_encode($manifest)
        );

        return redirect()->route('gespidieffe.unisciorganizza.editor-merge', ['session' => $session]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 1c – Aggiungi file a sessione esistente (GET)
    // ─────────────────────────────────────────────────────────────

    public function aggiungi(string $session)
    {
        $manifestPath = storage_path('app/' . $this->folder . '/' . $session . '_uo_manifest.json');
        abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return view('gespidieffe::unisciorganizza.upload', [
            'existingSession' => $session,
            'existingFiles'   => $manifest['files'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor merge (riordinamento file)
    // ─────────────────────────────────────────────────────────────

    public function editorMerge(string $session)
    {
        $manifestPath = storage_path('app/' . $this->folder . '/' . $session . '_uo_manifest.json');
        abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return view('gespidieffe::unisciorganizza.editor-merge', [
            'session'  => $session,
            'manifest' => $manifest,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve PDF singolo al browser (PDF.js, step 2)
    // ─────────────────────────────────────────────────────────────

    public function servePdfMerge(string $session, int $index)
    {
        $path = storage_path('app/' . $this->folder . '/' . $session . '_uo_f' . $index . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2→3 – Applica merge e redirect a editor-organizza
    // ─────────────────────────────────────────────────────────────

    public function applicaMerge(Request $request)
    {
        $request->validate([
            'session' => ['required', 'uuid'],
            'order'   => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'min:0'],
        ]);

        $session      = $request->input('session');
        $order        = $request->input('order');
        $base         = storage_path('app/' . $this->folder . '/');
        $manifestPath = $base . $session . '_uo_manifest.json';

        abort_unless(file_exists($manifestPath), 404, 'Sessione non trovata.');
        $manifest = json_decode(file_get_contents($manifestPath), true);

        $filesByIndex = [];
        foreach ($manifest['files'] as $f) {
            $filesByIndex[$f['index']] = $base . $f['filename'];
        }

        $orderedPaths = [];
        foreach ($order as $idx) {
            abort_unless(isset($filesByIndex[$idx]), 422, "File con indice $idx non trovato.");
            $orderedPaths[] = $filesByIndex[$idx];
        }

        foreach ($orderedPaths as $path) {
            abort_unless(file_exists($path), 404, 'File PDF non trovato: ' . basename($path));
        }

        // Merge con qpdf → file unito
        $mergedName = $session . '_uo_merged.pdf';
        $mergedPath = $base . $mergedName;

        $pageArgs = implode(' ', array_map(fn($f) => escapeshellarg($f), $orderedPaths));
        $null     = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $cmd      = sprintf(
            'qpdf --empty --pages %s -- %s 2>' . $null,
            $pageArgs,
            escapeshellarg($mergedPath)
        );
        exec($cmd);

        abort_unless(file_exists($mergedPath), 500, 'Merge fallito.');

        (new ContatorePdfService())->incrementa('merge');

        // Conta le pagine del merged per passarle all'editor-organizza
        $pages = $this->qpdfPageCount($mergedPath);

        return response()->json([
            'redirect' => route('gespidieffe.unisciorganizza.editor-organizza', ['session' => $session]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Editor organizza (griglia pagine del PDF unito)
    // ─────────────────────────────────────────────────────────────

    public function editorOrganizza(string $session)
    {
        $mergedPath = storage_path('app/' . $this->folder . '/' . $session . '_uo_merged.pdf');
        abort_unless(file_exists($mergedPath), 404, 'File unito non trovato. Tornare allo step precedente.');

        $pages = $this->qpdfPageCount($mergedPath);

        return view('gespidieffe::unisciorganizza.editor-organizza', compact('session', 'pages'));
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve il PDF unito al browser (PDF.js, step 3)
    // ─────────────────────────────────────────────────────────────

    public function servePdfOrganizza(string $session)
    {
        $path = storage_path('app/' . $this->folder . '/' . $session . '_uo_merged.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Applica organizzazione pagine (POST)
    // ─────────────────────────────────────────────────────────────

    public function applicaOrganizza(Request $request)
    {
        $request->validate([
            'session' => ['required', 'uuid'],
            'ordine'  => ['required', 'string', 'max:10000'],
        ]);

        $session    = $request->input('session');
        $base       = storage_path('app/' . $this->folder . '/');
        $src        = $base . $session . '_uo_merged.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $totPages = $this->qpdfPageCount($src);
        abort_if($totPages === 0, 422, 'Impossibile leggere le pagine del PDF.');

        $ordine = array_map('intval', explode(',', $request->input('ordine')));

        foreach ($ordine as $n) {
            abort_if($n < 1 || $n > $totPages, 422, "Numero di pagina non valido: {$n}.");
        }

        abort_if(empty($ordine), 422, 'Nessuna pagina specificata.');

        $paginaStr = implode(',', $ordine);
        $outName   = $session . '_uo_finale.pdf';
        $outPath   = $base . $outName;

        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $cmd  = sprintf(
            'qpdf %s --pages %s %s -- %s 2>' . $null,
            escapeshellarg($src),
            escapeshellarg($src),
            escapeshellarg($paginaStr),
            escapeshellarg($outPath)
        );
        exec($cmd);

        abort_unless(file_exists($outPath), 500, 'Elaborazione fallita.');

        (new ContatorePdfService())->incrementa('organizza');

        return response()->json([
            'download_token' => $session . '_uo_finale',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download PDF finale
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'documento_finale.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    // ─────────────────────────────────────────────────────────────
    // Elimina tutti i file temporanei della sessione
    // ─────────────────────────────────────────────────────────────

    public function elimina(string $session)
    {
        $base = storage_path('app/' . $this->folder . '/');

        foreach (glob($base . $session . '_uo*') as $f) {
            @unlink($f);
        }

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conta pagine con qpdf
    // ─────────────────────────────────────────────────────────────

    private function qpdfPageCount(string $pdfPath): int
    {
        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $out  = shell_exec(sprintf('qpdf --show-npages %s 2>' . $null, escapeshellarg($pdfPath)));

        return (int) trim($out ?? '0');
    }
}