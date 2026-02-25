<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', SchoolClass::class);
        $schoolClasses = SchoolClass::with('level')->paginate(5);
        return response()->json($schoolClasses, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manageSchoolClass', SchoolClass::class);
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'level_id' => 'required|exists:levels,id',
        ]);
        $data['created_by'] = Auth::id();
        $schoolClass = SchoolClass::create($data);
        return response()->json([
            'message' => 'تم انشاء الصف بنجاح',
            'data' => $schoolClass
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schoolClass = SchoolClass::findOrFail($id);
        $this->authorize('manageSchoolClass', $schoolClass);
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'level_id' => 'required|exists:levels,id',
        ]);
        $data['created_by'] = Auth::id();
        $schoolClass->update($data);
        return response()->json([
            'message' => 'تم تحديث الصف بنجاح',
            'data' => $schoolClass
        ], 202);
    }

    /**
     * Remove the specified resource from storage.
     */
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
