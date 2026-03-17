<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NumeraPdfController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::numera.upload');
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

        return redirect()->route('gespidieffe.numera.editor', ['file' => $uuid])
                         ->with('numera_meta', compact('pages', 'original'));
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Editor
    // ─────────────────────────────────────────────────────────────

    public function editor(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $pages    = $this->qpdfPageCount($path);
        $original = session('numera_meta.original', basename($path));

        return view('gespidieffe::numera.editor', compact('file', 'pages', 'original'));
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
    // Step 3 – Applica numerazione
    //
    // Strategia:
    //   1. TCPDF genera un PDF "overlay" trasparente con soli numeri
    //      (una pagina per ogni pagina del documento originale)
    //   2. pdftk stamp sovrappone l'overlay al PDF originale
    //      → testo originale selezionabile, qualità vettoriale intatta
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'file'          => ['required', 'uuid'],
            'formato'       => ['required', 'in:numero,pagina,totale,trattini'],
            'posizione'     => ['required', 'in:bl,bc,br,tl,tc,tr,ml,mc,mr'],
            'font_size'     => ['required', 'integer', 'min:6', 'max:24'],
            'prima_pagina'  => ['required', 'integer', 'min:1'],
            'numero_inizio' => ['required', 'integer', 'min:1'],
        ]);

        $uuid = $request->input('file');
        $base = storage_path('app/' . $this->folder . '/');
        $src  = $base . $uuid . '.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $totPages     = $this->qpdfPageCount($src);
        abort_if($totPages === 0, 422, 'Impossibile leggere le pagine del PDF.');

        $formato      = $request->input('formato');
        $posizione    = $request->input('posizione');
        $fontSize     = (int) $request->input('font_size');
        $primaPagina  = max(1, (int) $request->input('prima_pagina'));
        $numeroInizio = max(1, (int) $request->input('numero_inizio'));

        // ── 1. Legge dimensioni di ogni pagina con qpdf ──────────
        $pageSizes = $this->qpdfPageSizes($src, $totPages);

        // ── 2. Genera overlay PDF con TCPDF ──────────────────────
        $overlayPath = $base . $uuid . '_overlay.pdf';
        $this->generaOverlay(
            $overlayPath,
            $totPages,
            $pageSizes,
            $formato,
            $posizione,
            $fontSize,
            $primaPagina,
            $numeroInizio
        );

        abort_unless(file_exists($overlayPath), 500, 'Generazione overlay fallita.');

        // ── 3. pdftk stamp: sovrappone overlay su originale ──────
        $outPath = $base . $uuid . '_numerato.pdf';
        $cmd = sprintf(
            'pdftk %s multistamp %s output %s 2>/dev/null',
            escapeshellarg($src),
            escapeshellarg($overlayPath),
            escapeshellarg($outPath)
        );
        exec($cmd);

        @unlink($overlayPath);

        abort_unless(file_exists($outPath), 500, 'Elaborazione pdftk fallita.');

        return response()->json([
            'download_token' => $uuid . '_numerato',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download PDF risultante
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'numerato.pdf', [
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
    // Helper – genera il PDF overlay con TCPDF
    //
    // Ogni pagina ha le stesse dimensioni della pagina originale
    // e contiene solo il numero di pagina (sfondo trasparente).
    // ─────────────────────────────────────────────────────────────

    private function generaOverlay(
        string $outPath,
        int    $totPages,
        array  $pageSizes,
        string $formato,
        string $posizione,
        int    $fontSize,
        int    $primaPagina,
        int    $numeroInizio
    ): void {
        $pdf = new \TCPDF('', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GespidiEffe');
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $total = $totPages - $primaPagina + $numeroInizio;

        for ($pageNum = 1; $pageNum <= $totPages; $pageNum++) {
            $wMm = $pageSizes[$pageNum]['w'] ?? 210.0;
            $hMm = $pageSizes[$pageNum]['h'] ?? 297.0;

            $orientation = ($wMm > $hMm) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$wMm, $hMm]);

            // Pagine prima di prima_pagina: pagina vuota (nessun numero)
            if ($pageNum < $primaPagina) {
                continue;
            }

            $n     = $pageNum - $primaPagina + $numeroInizio;
            $label = $this->buildLabel($formato, $n, $total);

            $pdf->SetFont('helvetica', 'B', $fontSize);
            $pdf->SetTextColor(0, 0, 0);

            $cellW  = max(30.0, mb_strlen($label) * $fontSize * 0.35);
            $cellH  = $fontSize * 0.5;
            $margin = 6.0;

            [$x, $y, $align] = $this->calcolaXY($posizione, $wMm, $hMm, $margin, $cellW, $cellH);

            $pdf->SetXY($x, $y);
            $pdf->Cell($cellW, $cellH, $label, 0, 0, $align);
        }

        $pdf->Output($outPath, 'F');
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – legge dimensioni (mm) di ogni pagina con qpdf
    // ─────────────────────────────────────────────────────────────

    private function qpdfPageSizes(string $pdfPath, int $totPages): array
    {
        // qpdf --json emette le dimensioni in punti (pt)
        $json = shell_exec(sprintf('qpdf --json %s 2>/dev/null', escapeshellarg($pdfPath)));
        $sizes = [];

        if ($json) {
            $data = json_decode($json, true);
            $ptToMm = 25.4 / 72.0;

            foreach ($data['pages'] ?? [] as $idx => $page) {
                $mediabox = $page['mediabox'] ?? null;
                if ($mediabox && count($mediabox) === 4) {
                    $wPt = abs($mediabox[2] - $mediabox[0]);
                    $hPt = abs($mediabox[3] - $mediabox[1]);
                    $sizes[$idx + 1] = [
                        'w' => round($wPt * $ptToMm, 2),
                        'h' => round($hPt * $ptToMm, 2),
                    ];
                }
            }
        }

        // Fallback A4 per pagine non lette
        for ($i = 1; $i <= $totPages; $i++) {
            if (!isset($sizes[$i])) {
                $sizes[$i] = ['w' => 210.0, 'h' => 297.0];
            }
        }

        return $sizes;
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – costruisce l'etichetta del numero di pagina
    // ─────────────────────────────────────────────────────────────

    private function buildLabel(string $formato, int $n, int $total): string
    {
        return match ($formato) {
            'numero'   => (string) $n,
            'pagina'   => 'Pagina ' . $n,
            'totale'   => $n . ' / ' . $total,
            'trattini' => '- ' . $n . ' -',
            default    => (string) $n,
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – calcola x, y, align in base alla posizione
    // ─────────────────────────────────────────────────────────────

    private function calcolaXY(
        string $posizione,
        float  $w,
        float  $h,
        float  $margin,
        float  $cellW,
        float  $cellH
    ): array {
        return match ($posizione) {
            'bl' => [$margin,               $h - $margin - $cellH, 'L'],
            'bc' => [($w - $cellW) / 2,     $h - $margin - $cellH, 'C'],
            'br' => [$w - $margin - $cellW, $h - $margin - $cellH, 'R'],
            'tl' => [$margin,               $margin,               'L'],
            'tc' => [($w - $cellW) / 2,     $margin,               'C'],
            'tr' => [$w - $margin - $cellW, $margin,               'R'],
            'ml' => [$margin,               ($h - $cellH) / 2,     'L'],
            'mc' => [($w - $cellW) / 2,     ($h - $cellH) / 2,     'C'],
            'mr' => [$w - $margin - $cellW, ($h - $cellH) / 2,     'R'],
            default => [($w - $cellW) / 2,  $h - $margin - $cellH, 'C'],
        };
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
