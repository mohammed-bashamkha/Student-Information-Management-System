<?php

namespace App\Http\Controllers;

use App\Models\Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Exports\StudentErrorExport;
use App\Services\ErrorServices\ErrorService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ErrorResource;


class ErrorController extends Controller
{
    use AuthorizesRequests;
    protected $errorService;
    public function __construct(ErrorService $errorService)
    {
        $this->errorService = $errorService;
    }
    public function index(Request $request)
    {
        $errorsLog = $this->errorService->getErrors($request->all());
        return ErrorResource::collection($errorsLog)->additional([
            'message' => 'تم جلب سجل التصحيحات بنجاح'
        ]);
    }

    public function show(string $id)
    {
        $errorRecord = $this->errorService->getErrorById($id);
        return new ErrorResource($errorRecord);
    }
    public function destroy(string $id)
    {
        $errorRecord = $this->errorService->deleteError($id);
        return response()->json([
            'message' => 'تم حذف سجل خطأ بيانات الطالب بنجاح',
            'data' => $errorRecord->student->full_name
        ], 200);
    }

    public function exportStudentErrors(Request $request)
    {
        $this->authorize('errorsExport', Error::class);
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
