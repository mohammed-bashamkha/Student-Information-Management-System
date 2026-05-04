<?php

use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FinalResultController;
use App\Http\Controllers\FinalResultExportController;
use App\Http\Controllers\FinalResultImportController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentsDataImportController;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/export/student-data', [FinalResultExportController::class, 'exportStudentData'])
    ->name('student-data.export');
Route::get('/export/student-errors', [ErrorController::class, 'exportStudentErrors'])
    ->name('student-errors.export');
Route::get('/export/final-result', [FinalResultExportController::class, 'exportFinalResults'])
    ->name('final-result.export');



Route::get('/final-results', [FinalResultController::class, 'index']);
Route::get('/student/{id}', [StudentController::class, 'show'])->name('students.show');



Route::get('/students-index', [StudentsDataImportController::class, 'index'])->name('students.students-index');

// ===== PDF Export Routes =====
Route::prefix('pdf')->group(function () {
    Route::get('/certificate-replacement/{id}', [PdfExportController::class, 'certificateReplacement'])->name('pdf.certificate');
    Route::get('/transfer/{id}',                [PdfExportController::class, 'transfer'])->name('pdf.transfer');
    Route::get('/admission/{id}',               [PdfExportController::class, 'admission'])->name('pdf.admission');
    Route::get('/final-result/{id}',            [PdfExportController::class, 'finalResult'])->name('pdf.finalResult');
});
