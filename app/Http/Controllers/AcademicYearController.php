<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicYearRequest\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYearRequest\UpdateAcademicYearRequest;
use App\Services\AcademicYearServices\AcademicYearService;

class AcademicYearController extends Controller
{
    protected $academicYearService;
    public function __construct(AcademicYearService $academicYearService)
    {
        $this->academicYearService = $academicYearService;
    }
    public function index()
    {
        $academicYears = $this->academicYearService->getAcademicYears();
        return response()->json([
            'data' => $academicYears
        ], 200);
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $validateData = $request->validated();
        $academicYear = $this->academicYearService->createAcademicYear($validateData);
        return response()->json([
            'message' => 'تم اضافة السنة الدراسية بنجاح',
            'data' => $academicYear
        ], 201);
    }

    public function update(UpdateAcademicYearRequest $request, string $id)
    {
        $validateData = $request->validated();
        $academicYear = $this->academicYearService->updateAcademicYear($validateData, $id);
        return response()->json([
            'message' => 'تم تعديل السنة الدراسية بنجاح',
            'data' => $academicYear
        ], 202);
    }

    public function destroy(string $id)
    {
        $academicYear = $this->academicYearService->deleteAcademicYear($id);
        return response()->json([
            'message' => 'تم حدف السنة الدراسية بنجاح',
            'data' => $academicYear
        ], 200);
    }
}
