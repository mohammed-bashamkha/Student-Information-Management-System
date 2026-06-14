<?php

namespace App\Http\Controllers;

use App\Exports\FinalResultExport;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Exports\StudentDataExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;

class FinalResultExportController extends Controller
{
    use AuthorizesRequests;
    public function exportFinalResults(Request $request)
    {
        $this->authorize('finalResultExport', FinalResultExport::class);
        
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $schoolId = $request->school_id;
        $classId = $request->class_id;
        $academicYearId = $request->academic_year_id;

        $sId = School::where('id', $schoolId)->value('name');
        $cId = SchoolClass::where('id', $classId)->value('name');
        $aId = AcademicYear::where('id', $academicYearId)->value('year');

        $fileName = "النتائج_النهائية_للصف_{$cId}_لمدرسة_{$sId}_للعام_({$aId})_" . now()->format('Y-m-d') . '.xlsx';
        $fileName = str_replace(['/', '\\'], '-', $fileName);

        return Excel::download(
            new FinalResultExport($schoolId, $classId, $academicYearId),
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX,
            ['Access-Control-Expose-Headers' => 'Content-Disposition']
        );
    }


    public function exportStudentData(Request $request)
    {
        $this->authorize('studentExport', StudentDataExport::class);

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
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX,
            ['Access-Control-Expose-Headers' => 'Content-Disposition']
        );
    }
}
