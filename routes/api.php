<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinalResultController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolControlle;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('/export/final-result', [FinalResultController::class, 'export'])
//     ->name('final-result.export');

Route::post('/login',[AuthController::class,'login'])->name('login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    /**
     * @authenticated
     */
    // users routes
    Route::apiResource('/users',UserController::class);
    // roles routes
    Route::apiResource('/roles',RoleController::class);
    // academic year routes
    Route::apiResource('/academic-year',AcademicYearController::class);
    // schools routes
    Route::apiResource('/schools',SchoolControlle::class);
    // subjects routes
    Route::apiResource('/subjects',SubjectController::class);
    // school classes routes
    Route::apiResource('/school-classes',SchoolClassController::class);
    // grades routes
    Route::apiResource('/grades',GradeController::class);
    // students route
    Route::apiResource('/students',StudentController::class);
    Route::post('/students-search', [StudentController::class, 'studentsSearch'])->name('students.search');

    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    });
