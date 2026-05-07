<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalResultRequest\FinalResultImportRequest;
use App\Imports\FinalResultImport;
use App\Services\FinalResultImportServices\FinalResultImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinalResultImportController extends Controller
{
    protected $importService;

    public function __construct(FinalResultImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showImportForm()
    {
        return response()->json($this->importService->ImportForm());
    }

    public function importImproved(FinalResultImportRequest $request)
    {
        try {
            $result = $this->importService->importResults(
                $request->validated(),
                $request->file('file'),
                $request->boolean('preview')
            );

            if ($result['status'] === 'preview') {
                return response()->json($result);
            }

            $statusCode = $result['report']['summary']['failed'] > 0 ? 207 : 200;

            return response()->json([
                'message' => $result['message'],
                'import_report' => $result['report']
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

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
