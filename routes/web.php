<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportPDFController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/laporan/export/pdf', [ExportPDFController::class, 'export'])->name('laporan.export.pdf');

