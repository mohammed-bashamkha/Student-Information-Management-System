<?php

use App\Http\Controllers\FinalResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export/final-result', [FinalResultController::class, 'export'])
    ->name('final-result.export');

Route::get('/import/final-result', [FinalResultController::class, 'showImport'])
    ->name('import.form');

Route::post('/import/final-result', [FinalResultController::class, 'import'])
    ->name('import.submit');

Route::get('/final-results', [FinalResultController::class, 'index']);
