<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CensuraPdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::censura.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $token    = Str::uuid()->toString();
        $filename = $token . '.pdf';

        Storage::disk($this->disk)->putFileAs($this->folder, $request->file('pdf'), $filename);

        return redirect()->route('gespidieffe.censura.editor', ['file' => $token]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor
    // ─────────────────────────────────────────────────────────────

    public function editor(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $pageCount = $this->qpdfPageCount($path);
        abort_if($pageCount === 0, 500, 'Impossibile leggere il PDF.');

        return view('gespidieffe::censura.editor', [
            'token'     => $file,
            'pageCount' => $pageCount,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // API – Serve il PDF grezzo a PDF.js
    // ─────────────────────────────────────────────────────────────

    public function servePdf(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Applica censure (flusso ibrido qpdf + GS/TCPDF)
    //
    // Pagine SENZA censure → copiate con qpdf (vettoriale originale)
    // Pagine CON  censure  → rasterizzate con Ghostscript, rettangoli
    //                        sovrapposti con TCPDF
    // Merge finale         → qpdf concatena tutti i pezzi in ordine
    //
    // Per tornare al flusso "rasterizza tutto" basta chiamare
    // $this->applicaRasterizzaTutto($token, $rects, $srcPath)
    // al posto del codice ibrido qui sotto.
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'token'          => ['required', 'uuid'],
            'rects'          => ['required', 'array', 'min:1'],
            'rects.*.page'   => ['required', 'integer', 'min:1'],
            'rects.*.x'      => ['required', 'numeric'],
            'rects.*.y'      => ['required', 'numeric'],
            'rects.*.w'      => ['required', 'numeric', 'min:1'],
            'rects.*.h'      => ['required', 'numeric', 'min:1'],
            'rects.*.color'  => ['required', 'in:black,white'],
        ]);

        $token   = $request->input('token');
        $rects   = $request->input('rects');
        $srcPath = storage_path('app/' . $this->folder . '/' . $token . '.pdf');

        abort_unless(file_exists($srcPath), 404, 'File non trovato.');

        // Raggruppa rettangoli per pagina
        $rectsByPage = [];
        foreach ($rects as $rect) {
            $rectsByPage[(int) $rect['page']][] = $rect;
        }

        $base      = storage_path('app/' . $this->folder . '/');
        $pageCount = $this->qpdfPageCount($srcPath);
        abort_if($pageCount === 0, 500, 'Impossibile leggere il PDF.');

        $dpi = 200;

        // Lista di file PDF parziali che verranno uniti alla fine
        $parts = [];

        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        for ($p = 1; $p <= $pageCount; $p++) {
            $partPath = $base . $token . '_part_p' . $p . '.pdf';

            if (!isset($rectsByPage[$p])) {
                // ── Pagina originale: copia con qpdf (vettoriale, peso minimo) ──
                $cmd = sprintf(
                    'qpdf %s --pages %s %d -- %s 2>' . $null,
                    escapeshellarg($srcPath),
                    escapeshellarg($srcPath),
                    $p,
                    escapeshellarg($partPath)
                );
                exec($cmd);
            } else {
                // ── Pagina censurata: rasterizza → brucia i rettangoli nel PNG → PDF ──
                // I rettangoli vengono "bruciati" direttamente sui pixel dell'immagine
                // tramite GD, prima di creare il PDF. Nessun layer vettoriale separato:
                // il contenuto originale è irrecuperabile anche copiando dal PDF.
                $pngPath = $base . $token . '_rast_p' . $p . '.png';

                $gs = sprintf(
                    'gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r%d -dFirstPage=%d -dLastPage=%d -sOutputFile=%s %s 2>' . $null,
                    $dpi, $p, $p,
                    escapeshellarg($pngPath),
                    escapeshellarg($srcPath)
                );
                exec($gs);

                abort_unless(file_exists($pngPath), 500, "Impossibile rasterizzare la pagina $p.");

                // ── Brucia i rettangoli nel PNG con GD ──────────────────
                $img = imagecreatefrompng($pngPath);
                abort_unless($img !== false, 500, "Impossibile aprire il PNG della pagina $p.");

                $pxW = imagesx($img);
                $pxH = imagesy($img);
                $pxPerPt = $dpi / 72.0; // fattore di conversione pt → pixel

                foreach ($rectsByPage[$p] as $rect) {
                    $color = $rect['color'] === 'black'
                        ? imagecolorallocate($img, 0, 0, 0)
                        : imagecolorallocate($img, 255, 255, 255);

                    $x1 = (int) round((float) $rect['x'] * $pxPerPt);
                    $y1 = (int) round((float) $rect['y'] * $pxPerPt);
                    $x2 = (int) round(((float) $rect['x'] + (float) $rect['w']) * $pxPerPt);
                    $y2 = (int) round(((float) $rect['y'] + (float) $rect['h']) * $pxPerPt);

                    // Clamp ai bordi dell'immagine
                    $x1 = max(0, min($x1, $pxW - 1));
                    $y1 = max(0, min($y1, $pxH - 1));
                    $x2 = max(0, min($x2, $pxW - 1));
                    $y2 = max(0, min($y2, $pxH - 1));

                    imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
                }

                imagepng($img, $pngPath);
                imagedestroy($img);
                // ────────────────────────────────────────────────────────

                $wMm = ($pxW / $dpi) * 25.4;
                $hMm = ($pxH / $dpi) * 25.4;

                $pdf = new \TCPDF('', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator('GespidiEffe');
                $pdf->SetAutoPageBreak(false);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                $pdf->AddPage($wMm > $hMm ? 'L' : 'P', [$wMm, $hMm]);
                // Solo l'immagine — nessun rettangolo TCPDF sopra
                $pdf->Image($pngPath, 0, 0, $wMm, $hMm, 'PNG', '', '', false, 300, '', false, false, 0);

                $pdf->Output($partPath, 'F');
                @unlink($pngPath);
            }

            abort_unless(file_exists($partPath), 500, "Parte $p non generata.");
            $parts[] = $partPath;
        }

        // ── Merge finale con qpdf ──
        $outToken = $token . '_censurato';
        $outPath  = $base . $outToken . '.pdf';

        $pageArgs = implode(' ', array_map(fn($f) => escapeshellarg($f), $parts));
        $mergeCmd = sprintf(
            'qpdf --empty --pages %s -- %s 2>' . $null,
            $pageArgs,
            escapeshellarg($outPath)
        );
        exec($mergeCmd);

        // Pulizia file parziali
        foreach ($parts as $f) {
            @unlink($f);
        }

        abort_unless(file_exists($outPath), 500, 'Merge fallito.');

        return response()->json(['download_token' => $outToken]);
    }

    // ─────────────────────────────────────────────────────────────
    // FALLBACK – Rasterizza tutto (tutte le pagine diventano immagini)
    // Chiamare questo metodo al posto del blocco ibrido in applica()
    // se il flusso ibrido dovesse dare problemi.
    // ─────────────────────────────────────────────────────────────

    private function applicaRasterizzaTutto(string $token, array $rects, string $srcPath): string
    {
        $base      = storage_path('app/' . $this->folder . '/');
        $dpi       = 200;
        $ptToMm    = 25.4 / 72.0;

        $rectsByPage = [];
        foreach ($rects as $rect) {
            $rectsByPage[(int) $rect['page']][] = $rect;
        }

        $pngPattern = $base . $token . '_rast_p%d.png';
        exec(sprintf(
            'gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r%d -sOutputFile=%s %s 2>/dev/null',
            $dpi,
            escapeshellarg($pngPattern),
            escapeshellarg($srcPath)
        ));

        $pageCount = 0;
        while (file_exists(sprintf($base . $token . '_rast_p%d.png', $pageCount + 1))) {
            $pageCount++;
        }

        if ($pageCount === 0) {
            abort(500, 'Impossibile rasterizzare il PDF.');
        }

        $pdf = new \TCPDF('', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GespidiEffe');
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        for ($p = 1; $p <= $pageCount; $p++) {
            $pngPath = sprintf($base . $token . '_rast_p%d.png', $p);
            [$pxW, $pxH] = getimagesize($pngPath);
            $wMm = ($pxW / $dpi) * 25.4;
            $hMm = ($pxH / $dpi) * 25.4;

            $pdf->AddPage($wMm > $hMm ? 'L' : 'P', [$wMm, $hMm]);
            $pdf->Image($pngPath, 0, 0, $wMm, $hMm, 'PNG', '', '', false, 300, '', false, false, 0);

            if (isset($rectsByPage[$p])) {
                foreach ($rectsByPage[$p] as $rect) {
                    $color = $rect['color'] === 'black' ? [0, 0, 0] : [255, 255, 255];
                    $pdf->SetFillColor(...$color);
                    $pdf->Rect(
                        (float) $rect['x'] * $ptToMm,
                        (float) $rect['y'] * $ptToMm,
                        (float) $rect['w'] * $ptToMm,
                        (float) $rect['h'] * $ptToMm,
                        'F'
                    );
                }
            }

            @unlink($pngPath);
        }

        $outToken = $token . '_censurato';
        $outPath  = $base . $outToken . '.pdf';
        $pdf->Output($outPath, 'F');

        return $outToken;
    }

    // ─────────────────────────────────────────────────────────────
    // Download
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'documento_censurato.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    // ─────────────────────────────────────────────────────────────
    // Elimina file temporanei
    // ─────────────────────────────────────────────────────────────

    public function elimina(string $file)
    {
        $base = storage_path('app/' . $this->folder . '/');

        foreach (glob($base . $file . '*.pdf') as $f) {
            @unlink($f);
        }

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conta pagine con qpdf (supporta tutti i PDF moderni)
    // ─────────────────────────────────────────────────────────────

    private function qpdfPageCount(string $pdfPath): int
    {
        $out = shell_exec(sprintf('qpdf --show-npages %s 2>/dev/null', escapeshellarg($pdfPath)));

        return (int) trim($out ?? '0');
    }
}
