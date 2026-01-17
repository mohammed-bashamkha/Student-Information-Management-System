<?php

use App\Http\Controllers\FinalResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('/export/final-result', [FinalResultController::class, 'export'])
//     ->name('final-result.export');
