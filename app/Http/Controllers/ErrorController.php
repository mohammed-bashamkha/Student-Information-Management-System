<?php

namespace App\Http\Controllers;

use App\Models\Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Exports\StudentErrorExport;
use App\Services\ErrorServices\ErrorService;
use Maatwebsite\Excel\Facades\Excel;

class ErrorController extends Controller
{
    protected $errorService;
    public function __construct(ErrorService $errorService)
    {
        $this->errorService = $errorService;
    }
    public function index(Request $request)
    {
        $errorsLog = $this->errorService->getErrors($request->all());
        return response()->json([
            'message' => 'تم جلب سجل التصحيحات بنجاح',
            'data' => $errorsLog
        ], 200);
    }

    public function show(string $id)
    {
        $errorRecord = $this->errorService->getErrorById($id);
        return response()->json([
            'data' => $errorRecord
        ], 200);
    }
    public function destroy(string $id)
    {
        $errorRecord = $this->errorService->deleteError($id);
        return response()->json([
            'message' => 'تم حدف سجل خطاء بيانات الطالب بنجاح',
            'data' => $errorRecord->student()->full_name
        ], 200);
    }

    public function exportStudentErrors(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $fileName = 'Student_Errors_' . now()->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new StudentErrorExport(
                $request->academic_year_id
            ),
            $fileName
        );
    }
}
