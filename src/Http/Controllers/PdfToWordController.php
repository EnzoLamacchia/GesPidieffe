<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Elamacchia\Gespidieffe\Services\ContatorePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfToWordController extends Controller
{
    private string $disk   = 'local';
    private string $folder = 'gespidieffe/tmp';

    // ─────────────────────────────────────────────────────────────
    // Step 1 – Upload (pagina form)
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        return view('gespidieffe::pdftoword.upload');
    }

    // ─────────────────────────────────────────────────────────────
    // Step 1b – Upload (POST: riceve il file)
    // ─────────────────────────────────────────────────────────────

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:102400'],
        ]);

        $uuid     = Str::uuid()->toString();
        $filename = $uuid . '.pdf';
        $original = $request->file('pdf')->getClientOriginalName();

        Storage::disk($this->disk)->putFileAs($this->folder, $request->file('pdf'), $filename);

        return redirect()->route('gespidieffe.pdf2word.confirm', ['file' => $uuid])
                         ->with('pdf2word_meta', ['original' => $original]);
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 – Pagina di conferma / anteprima
    // ─────────────────────────────────────────────────────────────

    public function confirm(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.pdf');
        abort_unless(file_exists($path), 404, 'File non trovato.');

        $original = session('pdf2word_meta.original', basename($path));
        $tipo     = $this->rilevaTipoPdf($path);

        return view('gespidieffe::pdftoword.upload', compact('file', 'original', 'tipo'));
    }

    // ─────────────────────────────────────────────────────────────
    // Step 3 – Conversione (POST)
    // ─────────────────────────────────────────────────────────────

    public function applica(Request $request)
    {
        $request->validate([
            'file' => ['required', 'uuid'],
        ]);

        $uuid = $request->input('file');
        $base = storage_path('app/' . $this->folder . '/');
        $src  = $base . $uuid . '.pdf';

        abort_unless(file_exists($src), 404, 'File sorgente non trovato.');

        $tipo = $this->rilevaTipoPdf($src);

        if ($tipo === 'nativo') {
            $outPath = $this->convertiConLibreOffice($src, $base, $uuid);
        } elseif ($tipo === 'ibrido') {
            $outPath = $this->convertiIbrido($src, $base, $uuid);
        } else {
            $outPath = $this->convertiConOcr($src, $base, $uuid);
        }

        abort_if($outPath === null || ! file_exists($outPath), 500, 'Conversione fallita. Verificare che LibreOffice e Tesseract siano installati.');

        (new ContatorePdfService())->incrementa('pdf2word');

        return response()->json([
            'download_token' => $uuid . '_pdf2word',
            'tipo'           => $tipo,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download .docx
    // ─────────────────────────────────────────────────────────────

    public function download(string $file)
    {
        $path = storage_path('app/' . $this->folder . '/' . $file . '.docx');
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'documento_convertito.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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
    // Helper – rileva se il PDF ha testo nativo o è scansionato
    // ─────────────────────────────────────────────────────────────

    private function rilevaTipoPdf(string $pdfPath): string
    {
        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

        // ── 1. Numero pagine ──────────────────────────────────────
        $nPagine = (int) trim(shell_exec(sprintf(
            'qpdf --show-npages %s 2>' . $null,
            escapeshellarg($pdfPath)
        )) ?? '0');

        if ($nPagine === 0) {
            return 'scansionato';
        }

        // ── 2. Dimensioni pagina (punti PDF) ──────────────────────
        // pdfinfo restituisce "Page size: W x H pts"
        $pdfinfoOut = shell_exec(sprintf(
            'pdfinfo %s 2>' . $null,
            escapeshellarg($pdfPath)
        ));
        $paginaPtW = 595.0; // A4 default
        $paginaPtH = 842.0;
        if (preg_match('/Page size:\s+([\d.]+)\s+x\s+([\d.]+)\s+pts/', $pdfinfoOut ?? '', $m)) {
            $paginaPtW = (float) $m[1];
            $paginaPtH = (float) $m[2];
        }

        // ── 3. Analisi immagini con pdfimages -list ───────────────
        $imgOutput = shell_exec(sprintf(
            'pdfimages -list %s 2>' . $null,
            escapeshellarg($pdfPath)
        ));

        $pagineConImmagineFullPage = 0;

        foreach (explode("\n", trim($imgOutput ?? '')) as $riga) {
            // Riga dati: page num type width height color comp bpc enc interp obj x-ppi y-ppi
            if (! preg_match('/^\s*(\d+)\s+\d+\s+image\s+(\d+)\s+(\d+)\s+\S+\s+\d+\s+\d+\s+\S+\s+\S+\s+\d+\s+\d+\s+(\d+)\s+(\d+)/', $riga, $m)) {
                continue;
            }
            $imgW  = (int) $m[2]; // pixel
            $imgH  = (int) $m[3];
            $xppi  = (int) $m[4];
            $yppi  = (int) $m[5];

            if ($xppi === 0 || $yppi === 0) {
                continue;
            }

            // Converti dimensioni immagine da pixel a punti PDF (1 pt = 1/72 inch)
            $imgPtW = $imgW / $xppi * 72.0;
            $imgPtH = $imgH / $yppi * 72.0;

            // Copertura percentuale rispetto alla pagina
            $copertura = ($imgPtW * $imgPtH) / ($paginaPtW * $paginaPtH);

            if ($copertura >= 0.85) {
                $pagineConImmagineFullPage++;
            }
        }

        // ── 4. Testo estraibile ───────────────────────────────────
        $testoOutput = shell_exec(sprintf(
            'pdftotext %s - 2>' . $null,
            escapeshellarg($pdfPath)
        ));
        $nCaratteri = mb_strlen(preg_replace('/\s+/', '', trim($testoOutput ?? '')));
        $hasTesto   = $nCaratteri >= 20;

        // ── 5. Classificazione ────────────────────────────────────
        $tutteLePageneHannoImmagineFullPage = ($pagineConImmagineFullPage >= $nPagine);
        $almenoUnaImmagineFullPage          = ($pagineConImmagineFullPage > 0);

        if (! $almenoUnaImmagineFullPage) {
            // Nessuna immagine a piena pagina → PDF nativo vettoriale
            return $hasTesto ? 'nativo' : 'scansionato';
        }

        if ($tutteLePageneHannoImmagineFullPage && $hasTesto) {
            // Ogni pagina è un'immagine MA c'è testo OCR invisibile → ibrido (es. ScanSnap)
            return 'ibrido';
        }

        if ($tutteLePageneHannoImmagineFullPage && ! $hasTesto) {
            // Ogni pagina è un'immagine senza testo → scansionato puro
            return 'scansionato';
        }

        // Alcune pagine hanno immagine a piena pagina, altre no → ibrido
        return $hasTesto ? 'ibrido' : 'scansionato';
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conversione PDF nativo → DOCX con LibreOffice
    // ─────────────────────────────────────────────────────────────

    private function convertiConLibreOffice(string $src, string $base, string $uuid): ?string
    {
        $null     = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $destPath = $base . $uuid . '_pdf2word.docx';

        // Usa pdf2docx (Python) per conversione PDF nativo → DOCX
        // Mantiene layout, tabelle, immagini e formattazione
        $python = env('GESPIDIEFFE_PYTHON_BIN', 'python3.10');

        $script = sprintf(
            'from pdf2docx import Converter; cv = Converter(%s); cv.convert(%s); cv.close()',
            var_export($src, true),
            var_export($destPath, true)
        );

        $cmd = sprintf(
            '%s -c %s 2>' . $null,
            escapeshellarg($python),
            escapeshellarg($script)
        );
        exec($cmd, $output, $exitCode);

        if (! file_exists($destPath) || filesize($destPath) === 0) {
            return null;
        }

        return $destPath;
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conversione PDF ibrido → DOCX
    //          Estrae il testo OCR invisibile già presente con pdftotext
    //          e lo converte in DOCX con Pandoc (evita di rifare OCR)
    // ─────────────────────────────────────────────────────────────

    private function convertiIbrido(string $src, string $base, string $uuid): ?string
    {
        $null   = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $tmpDir = sys_get_temp_dir() . '/gespidieffe_ibrido_' . $uuid;
        @mkdir($tmpDir, 0755, true);

        // Estrai il testo OCR invisibile già presente nel PDF
        $txtPath = $tmpDir . '/testo.txt';
        $cmdPdf  = sprintf(
            'pdftotext -layout %s %s 2>' . $null,
            escapeshellarg($src),
            escapeshellarg($txtPath)
        );
        exec($cmdPdf);

        if (! file_exists($txtPath) || filesize($txtPath) === 0) {
            // Fallback a OCR se il testo non è estraibile
            return $this->convertiConOcr($src, $base, $uuid);
        }

        // Converti il testo in DOCX con Pandoc
        $docxPath = $tmpDir . '/testo.docx';
        $cmdPandoc = sprintf(
            'pandoc %s -o %s 2>' . $null,
            escapeshellarg($txtPath),
            escapeshellarg($docxPath)
        );
        exec($cmdPandoc);

        if (! file_exists($docxPath) || filesize($docxPath) === 0) {
            return null;
        }

        $destPath = $base . $uuid . '_pdf2word.docx';
        rename($docxPath, $destPath);

        foreach (glob($tmpDir . '/*') as $f) {
            @unlink($f);
        }
        @rmdir($tmpDir);

        return $destPath;
    }

    // ─────────────────────────────────────────────────────────────
    // Helper – conversione PDF scansionato → DOCX con OCR
    //          Ghostscript rasterizza → Tesseract OCR → LibreOffice
    // ─────────────────────────────────────────────────────────────

    private function convertiConOcr(string $src, string $base, string $uuid): ?string
    {
        putenv('HOME=/tmp');

        $null   = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $tmpDir = sys_get_temp_dir() . '/gespidieffe_ocr_' . $uuid;
        @mkdir($tmpDir, 0755, true);

        // Conta le pagine con qpdf
        $nPagine = (int) trim(shell_exec(sprintf(
            'qpdf --show-npages %s 2>' . $null,
            escapeshellarg($src)
        )) ?? '0');

        if ($nPagine === 0) {
            return null;
        }

        $testoCompleto = '';

        for ($p = 1; $p <= $nPagine; $p++) {
            // Rasterizza la pagina in PNG a 300 DPI
            $pngPath = $tmpDir . '/pagina_' . $p . '.png';
            $cmdGs   = sprintf(
                'gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -dFirstPage=%d -dLastPage=%d -sOutputFile=%s %s 2>' . $null,
                $p,
                $p,
                escapeshellarg($pngPath),
                escapeshellarg($src)
            );
            exec($cmdGs);

            if (! file_exists($pngPath)) {
                continue;
            }

            // OCR con Tesseract (ita + eng per documenti misti)
            $ocrBase = $tmpDir . '/ocr_' . $p;
            $cmdTes  = sprintf(
                'tesseract %s %s -l ita+eng 2>' . $null,
                escapeshellarg($pngPath),
                escapeshellarg($ocrBase)
            );
            exec($cmdTes);

            $txtPath = $ocrBase . '.txt';
            if (file_exists($txtPath)) {
                $testoCompleto .= "\n\n" . file_get_contents($txtPath);
            }

            @unlink($pngPath);
        }

        // Salva il testo in un file .txt temporaneo
        $txtTmp = $tmpDir . '/testo_completo.txt';
        file_put_contents($txtTmp, trim($testoCompleto));

        // Converti il .txt in .docx con LibreOffice
        $cmdLo = sprintf(
            'libreoffice --headless --convert-to docx %s --outdir %s 2>' . $null,
            escapeshellarg($txtTmp),
            escapeshellarg($tmpDir)
        );
        exec($cmdLo);

        $docxTmp = $tmpDir . '/testo_completo.docx';
        if (! file_exists($docxTmp)) {
            $files = glob($tmpDir . '/*.docx');
            if (empty($files)) {
                return null;
            }
            $docxTmp = $files[0];
        }

        $destPath = $base . $uuid . '_pdf2word.docx';
        rename($docxTmp, $destPath);

        // Pulizia cartella temporanea
        foreach (glob($tmpDir . '/*') as $f) {
            @unlink($f);
        }
        @rmdir($tmpDir);

        return $destPath;
    }
}