<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificateReplacementRequest\StoreCertificateReplacementRequest;
use App\Http\Requests\CertificateReplacementRequest\UpdateCertificateReplacementRequest;
use App\Services\CertificateReplacementServices\CertificateReplacementService;
use Illuminate\Http\Request;

class CertificateReplacementController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateReplacementService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(Request $request)
    {
        $certificates = $this->certificateService->getCertificates($request->all());
        return response()->json($certificates, 200);
    }

    public function store(StoreCertificateReplacementRequest $request)
    {
        $certificate = $this->certificateService->storeCertificate(
            $request->validated(),
            $request->file('student_image')
        );

        return response()->json([
            'message' => 'تم تسجيل إصدار الشهادة بنجاح',
            'data'    => $certificate->load(['student', 'school', 'academicYear'])
        ], 201);
    }

    public function show($id)
    {
        $certificate = $this->certificateService->getCertificateById($id);
        return response()->json(['data' => $certificate], 200);
    }

    public function update(UpdateCertificateReplacementRequest $request, $id)
    {
        $certificate = $this->certificateService->updateCertificate(
            $request->validated(),
            $id,
            $request->file('student_image')
        );

        return response()->json([
            'message' => 'تم تعديل بيانات الشهادة المصدرة بنجاح',
            'data'    => $certificate
        ], 200);
    }

    public function destroy($id)
    {
        $this->certificateService->deleteCertificate($id);

        return response()->json([
            'message' => 'تم إلغاء سجل الشهادة بنجاح'
        ], 200);
    }
}
