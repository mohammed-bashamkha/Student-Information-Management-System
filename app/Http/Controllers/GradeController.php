<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Grade::class);
        $grades = Grade::with(['student', 'subject', 'academicYear'])->paginate(5);
        return response()->json($grades);
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
        $this->authorize('create',Grade::class);
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'first_semester_total' => 'required|numeric|min:0|max:50',
            'second_semester_total' => 'required|numeric|min:0|max:50',
            'total' => 'required|numeric|min:0|max:100',
        ]);
        $data['created_by'] = Auth::id();
        $grade = Grade::create($data);
        return response()->json([
            'message' => 'تم اضافة الدرجة بنجاح',
            'data' => $grade
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grade = Grade::with(['student', 'subject', 'academicYear'])->findOrFail($id);
        $this->authorize('view', $grade);
        return response()->json($grade);
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
        $grade = Grade::findOrFail($id);
        $this->authorize('update', $grade);
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'first_semester_total' => 'required|numeric|min:0|max:50',
            'second_semester_total' => 'required|numeric|min:0|max:50',
            'total' => 'required|numeric|min:0|max:100',
        ]);
        $grade->update($data);
        return response()->json([
            'message' => 'تم تحديث الدرجة بنجاح',
            'data' => $grade
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('delete', $grade);
        $grade->delete();
        return response()->json([
            'message' => 'تم حذف الدرجة بنجاح',
            'ID' => $grade->id
        ]);
    }
}
