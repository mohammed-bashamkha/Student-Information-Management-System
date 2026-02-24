<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny',Subject::class);
        $subjects = Subject::with('schoolClass')->paginate(5);
        return response()->json($subjects, 200);
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
        $this->authorize('create',Subject::class);
        $data = $request->validate([
            'created_by' => 'exists:users,id',
            'level_id' => 'integer|required|exists:levels,id',
            'school_class_id' => 'integer|required|exists:school_classes,id',
            'name' => 'required|string|max:50',
        ]);
        $data['created_by'] = Auth::id();
        $subject = Subject::create($data);
        return response()->json([
            'message' => 'تم انشاء المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
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
        $subject = Subject::findOrFail($id);
        $this->authorize('update',$subject);
        $data = $request->validate([
            'created_by' => 'exists:users,id',
            'level_id' => 'sometimes|integer|exists:levels,id',
            'school_class_id' => 'sometimes|integer|exists:school_classes,id',
            'name' => 'sometimes|string|max:50',
        ]);
        $data['created_by'] = Auth::id();
        $subject->update($data);

        return response()->json([
            'message' => 'تم تعديل المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
        ], 202);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subject = Subject::findOrFail($id);
        $this->authorize('delete',$subject);
        $subject->delete();

        return response()->json([
            'message' => 'تم حدف المادة الدراسية بنجاح',
            'data' => $subject->name
        ], 200);
    }
}
