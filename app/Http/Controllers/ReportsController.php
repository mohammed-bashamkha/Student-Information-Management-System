<?php

namespace App\Http\Controllers;

use App\Services\ReportsServices\ReportsService;
use Illuminate\Http\Request;

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
        return response()->json($this->reportsService->finalResultsReport($request->all()));
    }
}
