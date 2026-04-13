<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolRequest\StoreSchoolRequest;
use App\Http\Requests\SchoolRequest\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolControlle extends Controller
{   
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny',School::class);
        $schools = School::paginate(5);
        return response()->json($schools, 200);
    }

    public function store(StoreSchoolRequest $request)
    {
        $this->authorize('create',School::class);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $school = School::create($data);
        return response()->json([
            'message' => 'تم اضافة المدرسة بنجاح',
            'data' => $school
        ], 201);
    }

    public function update(UpdateSchoolRequest $request, string $id)
    {
        $school = School::findOrFail($id);
        $this->authorize('update',$school);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $school->update($data);
        return response()->json([
            'message' => 'تم تعديل المدرسة بنجاح',
            'data' => $school
        ], 202);
    }

    public function destroy(string $id)
    {
        $school = School::findOrFail($id);
        $this->authorize('delete',$school);
        $school->delete();
        return response()->json([
            'message' => 'تم حدف السنة الدراسية بنجاح',
            'data' => $school->name
        ], 200);
    }
}
