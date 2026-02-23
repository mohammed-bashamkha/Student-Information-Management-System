<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request)
    {
        $this->authorize('manageAcademicYear');
        $data = $request->validate([
            'year' => "required|string|max:9|unique:academic_years,year",
            'start_date' => "required|string|date",
            'end_date' => "required|string|date",
            'status' => "nullable|boolean"
        ]);

        $academicYear = AcademicYear::create($data);
        return response()->json([
            'message' => 'تم اضافة السنة الدراسية بنجاح',
            'data' => $academicYear
        ], 201);
    }

    public function update(Request $request,string $id)
    {
        $this->authorize('manageAcademicYear');
        $academicYear = AcademicYear::findOrFail($id);
        $data = $request->validate([
            'year' => "sometimes|string|max:9|unique:academic_years,year,$id",
            'start_date' => "sometimes|date",
            'end_date' => "sometimes|date",
            'status' => "nullable|boolean"
        ]);

        $academicYear->update($data);
        return response()->json([
            'message' => 'تم تعديل السنة الدراسية بنجاح',
            'data' => $academicYear
        ], 202);
    }

    public function destroy(string $id)
    {
        $this->authorize('manageAcademicYear');
        $academicYear = AcademicYear::findOrFail($id);
        $academicYear->delete();
        return response()->json([
            'message' => 'تم حدف السنة الدراسية بنجاح',
            'data' => $academicYear->year
        ], 200);
    }
}
