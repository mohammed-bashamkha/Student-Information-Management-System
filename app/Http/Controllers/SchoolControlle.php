<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolRequest\StoreSchoolRequest;
use App\Http\Requests\SchoolRequest\UpdateSchoolRequest;
use App\Services\SchoolServices\SchoolService;

class SchoolControlle extends Controller
{   protected $schoolService;
    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }
    public function index()
    {
        $schools = $this->schoolService->getSchools();
        return response()->json($schools, 200);
    }

    public function store(StoreSchoolRequest $request)
    {
        $validateData = $request->validated();
        $school = $this->schoolService->storeSchool($validateData);
        return response()->json([
            'message' => 'تم اضافة المدرسة بنجاح',
            'data' => $school
        ], 201);
    }

    public function update(UpdateSchoolRequest $request, string $id)
    {
        $validateData = $request->validated();
        $school = $this->schoolService->updateSchool($validateData, $id);
        return response()->json([
            'message' => 'تم تعديل المدرسة بنجاح',
            'data' => $school
        ], 202);
    }

    public function destroy(string $id)
    {
        $school = $this->schoolService->deleteSchool($id);
        return response()->json([
            'message' => 'تم حدف السنة الدراسية بنجاح',
            'data' => $school
        ], 200);
    }
}
