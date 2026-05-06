<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassRequest\SchoolClassRequest;
use App\Services\SchoolClassServices\SchoolClassService;


class SchoolClassController extends Controller
{
    protected $schoolClassService;
    public function __construct(SchoolClassService $schoolClassService)
    {
        $this->schoolClassService = $schoolClassService;
    }
    public function index()
    {
        $schoolClasses = $this->schoolClassService->getSchoolClasses();
        return response()->json($schoolClasses, 200);
    }

    public function store(SchoolClassRequest $request)
    {
        $validateData = $request->validated();
        $schoolClass = $this->schoolClassService->storeSchoolClass($validateData);
        return response()->json([
            'message' => 'تم انشاء الصف بنجاح',
            'data' => $schoolClass
        ], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(SchoolClassRequest $request, string $id)
    {
        $validateData = $request->validated();
        $schoolClass = $this->schoolClassService->updateSchoolClass($validateData, $id);
        return response()->json([
            'message' => 'تم تحديث الصف بنجاح',
            'data' => $schoolClass
        ], 202);
    }
    
    public function destroy(string $id)
    {
        $schoolClass = $this->schoolClassService->deleteSchoolClass($id);
        return response()->json([
            'message' => 'تم حذف الصف بنجاح',
            'data' => $schoolClass->name
        ], 200);
    }
}
