<?php

namespace App\Http\Controllers;

use App\Exports\FinalResultExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinalResultExportController extends Controller
{
    public function exportFinalResults(Request $request)
    {
        // التحقق من أن الفلاتر الثلاثة تم إرسالها وهي موجودة في الجداول
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $schoolId = $request->school_id;
        $classId = $request->class_id;
        $academicYearId = $request->academic_year_id;

        $fileName = 'Final_Results_' . now()->format('Y_m_d_H_i') . '.xlsx';

        // استدعاء ملف التصدير مع تمرير الفلاتر
        return Excel::download(
            new FinalResultExport($schoolId, $classId, $academicYearId), 
            $fileName
        );
    }
}
