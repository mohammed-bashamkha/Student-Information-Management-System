<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest\StoreSubjectRequest;
use App\Http\Requests\SubjectRequest\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny',Subject::class);
        $subjects = Subject::with('schoolClass')->paginate(5);
        return response()->json($subjects, 200);
    }

    public function store(StoreSubjectRequest $request)
    {
        $this->authorize('create',Subject::class);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $subject = Subject::create($data);
        return response()->json([
            'message' => 'تم انشاء المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
        ], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(UpdateSubjectRequest $request, string $id)
    {
        $subject = Subject::findOrFail($id);
        $this->authorize('update',$subject);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $subject->update($data);

        return response()->json([
            'message' => 'تم تعديل المادة الدراسية بنجاح',
            'data' => $subject->load('schoolClass')
        ], 202);
    }

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
