<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransfersAdmissionRequest\RegisterStudentWithTransferRequest;
use App\Http\Requests\TransfersAdmissionRequest\StoreAdmissionRequest;
use App\Http\Requests\TransfersAdmissionRequest\StoreTransferRequest;
use App\Http\Requests\TransfersAdmissionRequest\UpdateTransfersAdmissionRequest;
use App\Services\RegisterStudentOutRegionService;
use App\Services\TransferAdmissionServices\TransferAdmissionService;
use Illuminate\Http\Request;

class TransferAdmissionController extends Controller
{
    protected $registerStudentOutRegionService;
    protected $transferAdmissionService;

    public function __construct(
        RegisterStudentOutRegionService $registerStudentOutRegionService,
        TransferAdmissionService $transferAdmissionService
    ) {
        $this->registerStudentOutRegionService = $registerStudentOutRegionService;
        $this->transferAdmissionService = $transferAdmissionService;
    }

    public function index(Request $request)
    {
        $transfers = $this->transferAdmissionService->getTransfersAdmissions($request->all());
        return response()->json($transfers, 200);
    }

    public function storeTransfer(StoreTransferRequest $request)
    {
        $result = $this->transferAdmissionService->storeTransfer($request->validated());

        $message = $result['status'] === 'approved'
            ? 'تم تسجيل طلب النقل وتمت الموافقة عليه مباشرةً'
            : 'تم تسجيل طلب النقل بنجاح وهو الآن قيد الانتظار';

        return response()->json([
            'message' => $message,
            'data'    => $result['transfer']
        ], 201);
    }

    public function storeAdmission(StoreAdmissionRequest $request)
    {
        $result = $this->transferAdmissionService->storeAdmission($request->validated());

        $message = $result['status'] === 'approved'
            ? 'تم تسجيل طلب القبول المؤقت وتمت الموافقة عليه مباشرةً'
            : 'تم تسجيل طلب القبول المؤقت بنجاح وهو الآن قيد الانتظار';

        return response()->json([
            'message' => $message,
            'data'    => $result['admission']
        ], 201);
    }

    public function update(UpdateTransfersAdmissionRequest $request, $id)
    {
        $result = $this->transferAdmissionService->updateTransferAdmission($request->validated(), $id);

        $statusMessages = [
            'approved' => 'تم قبول الطلب بنجاح وتم تحديث بيانات الطالب',
            'rejected' => 'تم رفض الطلب',
            'pending'  => 'تم إعادة فتح الطلب وهو الآن قيد الانتظار',
        ];

        return response()->json([
            'message' => $statusMessages[$result['status']],
            'data'    => $result['transferAdmission']
        ], 200);
    }

    public function show($id)
    {
        $transfer = $this->transferAdmissionService->getTransferAdmissionById($id);
        return response()->json($transfer, 200);
    }

    public function destroy($id)
    {
        $this->transferAdmissionService->deleteTransferAdmission($id);
        return response()->json(['message' => 'تم حذف الطلب بنجاح'], 200);
    }

    public function registerStudentOutRegion(RegisterStudentWithTransferRequest $request)
    {
        $data = $request->validated();
        $result = $this->registerStudentOutRegionService->registerStudentWithTransfer($data);

        return response()->json([
            'message' => 'تم تسجيل الطالب بنجاح',
            'data' => $result,
        ], 201);
    }
}
