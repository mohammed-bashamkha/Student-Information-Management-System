<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class StudentsDataImportController extends Controller
{
    public function index(Request $request)
    {
        $students = \App\Models\Student::with(['currentEnrollment.school', 'currentEnrollment.schoolClass', 'currentEnrollment.academicYear'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('students.index', compact('students'));
    }

    public function importForm()
    {
        $schools = School::all();
        $school_classes = SchoolClass::all();
        $academicYears = AcademicYear::all();

        return view('students.import', compact('schools', 'school_classes', 'academicYears'));
    }

    /**
     * معالجة ملف الإكسل المرفوع
     */
    public function import(Request $request)
    {
        // 1. التحقق من المدخلات
        $request->validate([
            'file'             => 'required|mimes:xlsx,xls,csv|max:10240', // حد أقصى 10 ميجا
            'school_id'        => 'required|exists:schools,id',
            'class_id'         => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ], [
            'file.required' => 'يرجى إرفاق ملف الإكسل.',
            'file.mimes'    => 'صيغة الملف غير مدعومة. يرجى رفع ملف إكسل صحيح.',
        ]);

        try {
            // 2. تهيئة كلاس الاستيراد مع البيانات المطلوبة
            $import = new StudentsImport(
                $request->school_id,
                $request->class_id,
                $request->academic_year_id
            );

            if ($request->boolean('preview')) {
                $import->preview = true;
            }

            // 3. تنفيذ الاستيراد
            Excel::import($import, $request->file('file'));

            // 4. تجهيز التقرير النهائي
            $stats = $import->stats;
            $successRate = $stats['total_rows'] > 0
                ? round(($stats['successful'] / $stats['total_rows']) * 100, 2)
                : 0;

            $report = [
                'summary' => [
                    'total_rows'       => $stats['total_rows'],
                    'successful'       => $stats['successful'],
                    'failed'           => $stats['failed'],
                    'skipped'          => $stats['skipped'] ?? 0,
                    'students_created' => $stats['students_created'],
                    'students_updated' => $stats['students_updated'],
                    'success_rate'     => $successRate
                ],
                'errors'   => $stats['errors'] ?? [],
                'warnings' => $stats['warnings'] ?? []
            ];

            if ($request->boolean('preview')) {
                return response()->json([
                    'status' => 'preview',
                    'report' => $report,
                    'sample_data' => $import->previewData,
                ]);
            }

            // 5. إعادة التوجيه مع رسالة النجاح والتقرير
            return back()->with([
                'success'       => 'تم الانتهاء من عملية استيراد الطلاب بنجاح.',
                'import_report' => $report
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // أخطاء مكتبة إكسل
            $failures = $e->failures();
            Log::error('Excel Validation Error', ['failures' => $failures]);
            return back()->with('error', 'يوجد خطأ في تركيبة ملف الإكسل. يرجى مراجعة البيانات.');
        } catch (\Exception $e) {
            // أي أخطاء أخرى (مثل توقف قاعدة البيانات)
            Log::error('Import Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ غير متوقع أثناء الاستيراد: ' . $e->getMessage());
        }
    }
}
