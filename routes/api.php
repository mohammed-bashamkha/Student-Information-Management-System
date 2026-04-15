<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateReplacementController;
use App\Http\Controllers\FinalResultController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolControlle;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\TransferAdmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('/export/final-result', [FinalResultController::class, 'export'])
//     ->name('final-result.export');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    /**
     * @authenticated
     */
    // users routes
    Route::apiResource('/users', UserController::class);
    // roles routes
    Route::apiResource('/roles', RoleController::class);
    // academic year routes
    Route::apiResource('/academic-year', AcademicYearController::class);
    // schools routes
    Route::apiResource('/schools', SchoolControlle::class);
    // subjects routes
    Route::apiResource('/subjects', SubjectController::class);
    // school classes routes
    Route::apiResource('/school-classes', SchoolClassController::class);
    // grades routes
    Route::apiResource('/grades', GradeController::class);
    // students route
    Route::apiResource('/students', StudentController::class);
    // certificate replacements routes
    Route::apiResource('/certificate-replacements', CertificateReplacementController::class);
    // transfers admissions routes
    Route::apiResource('/transfers-admissions', TransferAdmissionController::class)->except('store');
    Route::post('/transfers', [TransferAdmissionController::class, 'storeTransfer']);
    Route::post('/admissions', [TransferAdmissionController::class, 'storeAdmission']);

    // ===== PDF Export Routes =====
    Route::prefix('pdf')->group(function () {
        Route::get('/certificate-replacement/{id}', [PdfExportController::class, 'certificateReplacement'])->name('pdf.certificate');
        Route::get('/transfer/{id}',                [PdfExportController::class, 'transfer'])->name('pdf.transfer');
        Route::get('/admission/{id}',               [PdfExportController::class, 'admission'])->name('pdf.admission');
        Route::get('/final-result/{id}',            [PdfExportController::class, 'finalResult'])->name('pdf.finalResult');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
