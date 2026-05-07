<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest\StoreStudentWithEnrollmentRequest;
use App\Http\Requests\StudentRequest\UpdateStudentWithEnrollmentRequest;
use App\Services\StudentServices\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        $students = $this->studentService->getStudents($request->all());
        return response()->json($students, 200);
    }

    public function store(StoreStudentWithEnrollmentRequest $request)
    {
        $result = $this->studentService->storeStudent($request->validated());

        return response()->json([
            'message' => 'تم اضافة الطالب بنجاح',
            'data' => $result['student'],
            'school_enrollment' => $result['enrollment']->school->name,
            'class_enrollment' => $result['enrollment']->schoolClass->name
        ], 201);
    }

    public function show(string $id)
    {
        $student = $this->studentService->getStudentById($id);

        return response()->json([
            'message' => 'تم جلب بيانات الطالب بنجاح',
            'data' => $student
        ], 200);
    }

    public function update(UpdateStudentWithEnrollmentRequest $request, $id)
    {
        $student = $this->studentService->updateStudent($request->validated(), $id);

        return response()->json([
            'message' => 'تم تعديل بيانات الطالب والتسجيل بنجاح',
            'data' => $student
        ], 200);
    }

    public function destroy(string $id)
    {
        $student = $this->studentService->deleteStudent($id);

        return response()->json([
            'message' => 'تم حذف الطالب بنجاح',
            'data' => $student->only('school_number', 'full_name')
        ], 200);
    }
}
