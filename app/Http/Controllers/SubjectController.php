<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest\StoreSubjectRequest;
use App\Http\Requests\SubjectRequest\UpdateSubjectRequest;
use App\Services\SubjectServices\SubjectService;

class SubjectController extends Controller
{
    protected $subjectService;
    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }
    public function index()
    {
        $subjects = $this->subjectService->getSubjects();
        return response()->json($subjects, 200);
    }

    public function store(StoreSubjectRequest $request)
    {
        $validateData = $request->validated();
        $subject = $this->subjectService->storeSubject($validateData);
        return response()->json([
            'message' => 'تم انشاء المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
        ], 201);
    }

    public function update(UpdateSubjectRequest $request, string $id)
    {
        $validateData = $request->validated();
        $subject = $this->subjectService->updateSubject($validateData, $id);

        return response()->json([
            'message' => 'تم تعديل المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
        ], 202);
    }

    public function destroy(string $id)
    {
        $subject = $this->subjectService->deleteSubject($id);
        return response()->json([
            'message' => 'تم حدف المادة الدراسية بنجاح',
            'data' => $subject->name
        ], 200);
    }
}
