<?php

use App\Http\Controllers\FinalResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export/final-result', [FinalResultController::class, 'export'])
    ->name('final-result.export');
