<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest\StoreGradeRequest;
use App\Http\Requests\GradeRequest\UpdateGradeRequest;
use App\Services\GradeServices\GradeService;

class GradeController extends Controller
{
    protected $gradeService;
    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }
    public function index()
    {
        $grades = $this->gradeService->getGrades();
        return response()->json($grades);
    }

    public function store(StoreGradeRequest $request)
    {
        $grade = $this->gradeService->storeGrade($request->validated());
        return response()->json([
            'message' => 'تم إضافة الدرجة بنجاح',
            'data' => $grade
        ], 201);
    }

    public function show(string $id)
    {
        $grade = $this->gradeService->getGradeById($id);
        return response()->json($grade);
    }

    public function update(UpdateGradeRequest $request, string $id)
    {
        $grade = $this->gradeService->updateGrade($request->validated(), $id);
        return response()->json([
            'message' => 'تم تحديث الدرجة بنجاح',
            'data' => $grade
        ]);
    }

    public function destroy(string $id)
    {
        $grade = $this->gradeService->deleteGrade($id);
        return response()->json([
            'message' => 'تم حذف الدرجة بنجاح',
            'ID'      => $grade->id
        ]);
    }
}
