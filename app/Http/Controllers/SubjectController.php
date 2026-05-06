<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest\StoreSubjectRequest;
use App\Http\Requests\SubjectRequest\UpdateSubjectRequest;
use App\Services\SubjectServices\SubjectService;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    protected $subjectService;
    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }
    public function index(Request $request): JsonResponse
    {
        $subjects = $this->subjectService->getSubjects($request->all());
        return response()->json($subjects, 200);
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $validateData = $request->validated();
        $subject = $this->subjectService->storeSubject($validateData);
        return response()->json([
            'message' => 'تم انشاء المادة الدراسية بنجاح',
            'data' => $subject->load(['schoolClasses', 'level'])
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $subject = $this->subjectService->getSubjectById($id);
        return response()->json($subject, 200);
    }

    public function update(UpdateSubjectRequest $request, string $id): JsonResponse
    {
        $validateData = $request->validated();
        $subject = $this->subjectService->updateSubject($validateData, $id);

        return response()->json([
            'message' => 'تم تعديل المادة الدراسية بنجاح',
            'data' => $subject->load(['schoolClasses', 'level'])
        ], 202);
    }

    public function destroy(string $id)
    {
        try {
            $subject = $this->subjectService->deleteSubject($id);
            return response()->json([
                'message' => 'تم حذف المادة الدراسية بنجاح',
                'data' => $subject->name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
