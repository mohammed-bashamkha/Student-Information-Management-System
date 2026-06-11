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

        return Excel::download(
            new StudentDataExport(
                $request->school_id,
                $request->class_id,
                $request->academic_year_id
            ),
            $fileName
        );
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
