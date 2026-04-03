<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', SchoolClass::class);
        $schoolClasses = SchoolClass::with('level')->paginate(5);
        return response()->json($schoolClasses, 200);
    }

    public function store(SchoolClassRequest $request)
    {
        $this->authorize('manageSchoolClass', SchoolClass::class);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $schoolClass = SchoolClass::create($data);
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
        $schoolClass = SchoolClass::findOrFail($id);
        $this->authorize('manageSchoolClass', $schoolClass);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $schoolClass->update($data);
        return response()->json([
            'message' => 'تم تحديث الصف بنجاح',
            'data' => $schoolClass
        ], 202);
    }
    
    public function destroy(string $id)
    {
        $schoolClass = SchoolClass::findOrFail($id);
        $this->authorize('manageSchoolClass', $schoolClass);
        $schoolClass->delete();
        return response()->json([
            'message' => 'تم حذف الصف بنجاح',
            'data' => $schoolClass->name
        ], 200);
    }
}
