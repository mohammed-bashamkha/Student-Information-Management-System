<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalResultRequest\FinalResultImportRequest;
use App\Imports\FinalResultImport;
use App\Imports\FinalResultImportImproved;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class FinalResultImportController extends Controller
{
    public function showImportForm()
    {
        return view('import');
    }

    public function importImproved(FinalResultImportRequest $request)
    {
        try {
            $import = new FinalResultImportImproved(
                $request->academic_year_id,
                $request->class_id,
                $request->school_id
            );

            Excel::import($import, $request->file('file'));

            // الحصول على تقرير الاستيراد
            $report = $import->getImportReport();

            // التحقق من وجود أخطاء
            if ($report['summary']['failed'] > 0) {
                return redirect()->back()
                    ->with('warning', 'تم الاستيراد مع بعض الأخطاء')
                    ->with('import_report', $report);
            }

            return redirect()->back()
                ->with('success', 'تم استيراد النتائج النهائية بنجاح')
                ->with('import_report', $report);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * تصدير تقرير الاستيراد إلى JSON
     */
    public function exportImportReport(Request $request)
    {
        if (!$request->session()->has('import_report')) {
            return response()->json(['error' => 'لا يوجد تقرير استيراد متاح'], 404);
        }

        $report = $request->session()->get('import_report');
        
        return response()->json($report, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="import-report-' . date('Y-m-d-H-i-s') . '.json"'
        ]);
    }
}
