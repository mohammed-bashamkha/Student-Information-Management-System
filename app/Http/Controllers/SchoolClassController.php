<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassRequest\SchoolClassRequest;
use App\Services\SchoolClassServices\SchoolClassService;

use Spatie\ResponseCache\Facades\ResponseCache;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SchoolClassController extends Controller implements HasMiddleware
{
    protected $schoolClassService;
    public function __construct(SchoolClassService $schoolClassService)
    {
        $this->schoolClassService = $schoolClassService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('cacheResponse:86400', only: ['index', 'show']),
        ];
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
        ResponseCache::clear();
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
        ResponseCache::clear();
        return response()->json([
            'message' => 'تم تحديث الصف بنجاح',
            'data' => $schoolClass
        ], 202);
    }
    
    public function destroy(string $id)
    {
        $schoolClass = $this->schoolClassService->deleteSchoolClass($id);
        ResponseCache::clear();
        return response()->json([
            'message' => 'تم حذف الصف بنجاح',
            'data' => $schoolClass->name
        ], 200);
    }
}
