<?php

namespace App\Http\Controllers;

use App\Exports\StudentDataExport;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentsDataExportController extends Controller
{
    public function export_students_data(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $fileName = 'Student_Data_' . now()->format('Y_m_d') . '.xlsx';
        $cacheKey = "excel_export_{$request->school_id}_{$request->class_id}_{$request->academic_year_id}";

        $fileContent = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($request) {
            return Excel::raw(
                new StudentDataExport(
                    $request->school_id,
                    $request->class_id,
                    $request->academic_year_id
                ),
                \Maatwebsite\Excel\Excel::XLSX
            );
        });

        return response($fileContent)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function exportForm()
    {
        $schools = School::all();
        $school_classes = SchoolClass::all();
        $academicYears = AcademicYear::all();

        return response()->json([
            'schools' => $schools,
            'school_classes' => $school_classes,
            'academicYears' => $academicYears,
        ]);
    }
}
