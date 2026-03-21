<?php

use Elamacchia\Gespidieffe\Http\Controllers\CensuraPdfController;
use Elamacchia\Gespidieffe\Http\Controllers\GespidieffeController;
use Elamacchia\Gespidieffe\Http\Controllers\MergePdfController;
use Elamacchia\Gespidieffe\Http\Controllers\NumeraPdfController;
use Elamacchia\Gespidieffe\Http\Controllers\OrganizzaPdfController;
use Elamacchia\Gespidieffe\Http\Controllers\RuotaPdfController;
use Elamacchia\Gespidieffe\Http\Controllers\SplitPdfController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:sanctum', 'verified'],
    'prefix'     => 'gespidieffe',
    'as'         => 'gespidieffe.',
], function () {

    // --- Home ---
    Route::get('/', [GespidieffeController::class, 'index'])->name('home');

    // --- Censura PDF ---
    Route::get('/censura',               [CensuraPdfController::class, 'index'])->name('censura');
    Route::post('/censura/upload',       [CensuraPdfController::class, 'upload'])->name('censura.upload');
    Route::get('/censura/editor/{file}', [CensuraPdfController::class, 'editor'])->name('censura.editor');
    Route::post('/censura/applica',      [CensuraPdfController::class, 'applica'])->name('censura.applica');
    Route::get('/censura/download/{file}', [CensuraPdfController::class, 'download'])->name('censura.download');
    Route::match(['delete', 'post'], '/censura/elimina/{file}', [CensuraPdfController::class, 'elimina'])->name('censura.elimina');

    // Serve il file PDF grezzo al browser (usato da PDF.js nell'editor)
    Route::get('/censura/pdf/{file}', [CensuraPdfController::class, 'servePdf'])->name('censura.pdf');

    // --- Merge PDF ---
    Route::get('/merge',                              [MergePdfController::class, 'index'])->name('merge');
    Route::post('/merge/upload',                      [MergePdfController::class, 'upload'])->name('merge.upload');
    Route::get('/merge/editor/{session}',             [MergePdfController::class, 'editor'])->name('merge.editor');
    Route::get('/merge/aggiungi/{session}',           [MergePdfController::class, 'aggiungi'])->name('merge.aggiungi');
    Route::post('/merge/applica',                     [MergePdfController::class, 'applica'])->name('merge.applica');
    Route::get('/merge/download/{file}',              [MergePdfController::class, 'download'])->name('merge.download');
    Route::match(['delete', 'post'], '/merge/elimina/{session}', [MergePdfController::class, 'elimina'])->name('merge.elimina');
    Route::get('/merge/pdf/{session}/{index}',        [MergePdfController::class, 'servePdf'])->name('merge.pdf');

    // --- Split PDF ---
    Route::get('/split',                                        [SplitPdfController::class, 'index'])->name('split');
    Route::post('/split/upload',                                [SplitPdfController::class, 'upload'])->name('split.upload');
    Route::get('/split/editor/{file}',                          [SplitPdfController::class, 'editor'])->name('split.editor');
    Route::post('/split/applica',                               [SplitPdfController::class, 'applica'])->name('split.applica');
    Route::get('/split/download/{file}',                        [SplitPdfController::class, 'download'])->name('split.download');
    Route::get('/split/download-zip/{file}',                    [SplitPdfController::class, 'downloadZip'])->name('split.download-zip');
    Route::match(['delete', 'post'], '/split/elimina/{file}',   [SplitPdfController::class, 'elimina'])->name('split.elimina');
    Route::get('/split/pdf/{file}',                             [SplitPdfController::class, 'servePdf'])->name('split.pdf');

    // --- Organizza pagine ---
    Route::get('/organizza',                                          [OrganizzaPdfController::class, 'index'])->name('organizza');
    Route::post('/organizza/upload',                                  [OrganizzaPdfController::class, 'upload'])->name('organizza.upload');
    Route::get('/organizza/editor/{file}',                            [OrganizzaPdfController::class, 'editor'])->name('organizza.editor');
    Route::post('/organizza/applica',                                 [OrganizzaPdfController::class, 'applica'])->name('organizza.applica');
    Route::get('/organizza/download/{file}',                          [OrganizzaPdfController::class, 'download'])->name('organizza.download');
    Route::match(['delete', 'post'], '/organizza/elimina/{file}',     [OrganizzaPdfController::class, 'elimina'])->name('organizza.elimina');
    Route::get('/organizza/pdf/{file}',                               [OrganizzaPdfController::class, 'servePdf'])->name('organizza.pdf');

    // --- Ruota pagine ---
    Route::get('/ruota',                                          [RuotaPdfController::class, 'index'])->name('ruota');
    Route::post('/ruota/upload',                                  [RuotaPdfController::class, 'upload'])->name('ruota.upload');
    Route::get('/ruota/editor/{file}',                            [RuotaPdfController::class, 'editor'])->name('ruota.editor');
    Route::post('/ruota/applica',                                 [RuotaPdfController::class, 'applica'])->name('ruota.applica');
    Route::get('/ruota/download/{file}',                          [RuotaPdfController::class, 'download'])->name('ruota.download');
    Route::match(['delete', 'post'], '/ruota/elimina/{file}',     [RuotaPdfController::class, 'elimina'])->name('ruota.elimina');
    Route::get('/ruota/pdf/{file}',                               [RuotaPdfController::class, 'servePdf'])->name('ruota.pdf');

    // --- Numera pagine ---
    Route::get('/numera',                                          [NumeraPdfController::class, 'index'])->name('numera');
    Route::post('/numera/upload',                                  [NumeraPdfController::class, 'upload'])->name('numera.upload');
    Route::get('/numera/editor/{file}',                            [NumeraPdfController::class, 'editor'])->name('numera.editor');
    Route::post('/numera/applica',                                 [NumeraPdfController::class, 'applica'])->name('numera.applica');
    Route::get('/numera/download/{file}',                          [NumeraPdfController::class, 'download'])->name('numera.download');
    Route::match(['delete', 'post'], '/numera/elimina/{file}',     [NumeraPdfController::class, 'elimina'])->name('numera.elimina');
    Route::get('/numera/pdf/{file}',                               [NumeraPdfController::class, 'servePdf'])->name('numera.pdf');

});
