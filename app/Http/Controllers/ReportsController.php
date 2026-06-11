<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\ReportsServices\ReportsService;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class ReportsController extends Controller
{
    protected $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }

    public function studentsReport(Request $request)
    {
        return response()->json($this->reportsService->studentsReport($request->all()));
    }

    public function schoolsReport(Request $request)
    {
        return response()->json($this->reportsService->schoolsReport($request->all()));
    }

    public function transfersAdmissionsReport(Request $request)
    {
        return response()->json($this->reportsService->transfersAdmissionsReport($request->all()));
    }

    public function finalResultsReport(Request $request)
    {
        if (class_exists(\Spatie\ResponseCache\Facades\ResponseCache::class)) {
            \Spatie\ResponseCache\Facades\ResponseCache::clear();
        }
        return response()->json($this->reportsService->finalResultsReport($request->all()));
    }

    /**
     * تصدير التقرير الإحصائي الشامل PDF
     * GET /api/reports/pdf/comprehensive
     */
    public function comprehensivePdfReport(Request $request)
    {
        $filters = $request->all();

        // ─── Academic Year ───
        $activeYearId   = AcademicYear::where('status', 'active')->value('id');
        $academicYearId = $filters['academic_year_id'] ?? $activeYearId;
        $academicYear   = AcademicYear::find($academicYearId);

        // ─── 1. Students stats only ───
        $studentsData  = $this->reportsService->studentsReport(
            array_merge($filters, ['academic_year_id' => $academicYearId])
        );
        $studentsStats = $studentsData['stats'];

        // ─── 2. Schools stats only ───
        $schoolsData  = $this->reportsService->schoolsReport($filters);
        $schoolsStats = $schoolsData['stats'];

        // ─── 3. Transfers stats only ───
        $transfersData  = $this->reportsService->transfersAdmissionsReport(
            array_merge($filters, ['academic_year_id' => $academicYearId])
        );
        $transfersStats = $transfersData['stats'];

        // ─── 4. Results stats only ───
        $resultsData  = $this->reportsService->finalResultsReport(
            array_merge($filters, ['academic_year_id' => $academicYearId])
        );
        $resultsStats = $resultsData['stats'];

        $filename = 'التقرير-الإحصائي-الشامل-' . now()->format('Y-m-d') . '.pdf';

        return Pdf::view('PDF.report-comprehensive', compact(
            'academicYear',
            'studentsStats',
            'schoolsStats',
            'transfersStats',
            'resultsStats'
        ))
            ->format('a4')
            ->name($filename)
            ->download();
    }
}
