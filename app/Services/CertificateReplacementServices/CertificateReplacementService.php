<?php

namespace App\Services\CertificateReplacementServices;

use App\Models\CertificateReplacement;
use App\Traits\UploadFileTrait;
use App\Services\ActivityLogServices\ActivityLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CertificateReplacementService
{
    use AuthorizesRequests;
    use UploadFileTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getCertificates(array $filters = [])
    {
        $this->authorize('viewAny', CertificateReplacement::class);

        $query = CertificateReplacement::with(['student', 'school', 'schoolClass', 'academicYear', 'createdBy']);

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('seat_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['certificate_type'])) {
            $query->where('certificate_type', $filters['certificate_type']);
        }

        return $query->latest()->paginate(15);
    }

    public function storeCertificate(array $data, $imageFile = null)
    {
        $this->authorize('create', CertificateReplacement::class);

        $data['created_by'] = Auth::id();

        if ($imageFile) {
            $data['student_image'] = $this->uploadFile($imageFile, 'certificate_replacements/students-images');
        }

        $certificate = CertificateReplacement::create($data);

        $certificate->load('student');
        $this->activityLogService->logAction(
            'certificate_replacements',
            $certificate,
            'create',
            "تم تسجيل إصدار شهادة (بدل فاقد/تالف) للطالب: {$certificate->student->full_name}"
        );

        return $certificate;
    }

    public function getCertificateById($id)
    {
        $certificate = CertificateReplacement::with(['student', 'school', 'schoolClass', 'academicYear', 'createdBy'])->findOrFail($id);
        $this->authorize('view', $certificate);
        return $certificate;
    }

    public function updateCertificate(array $data, $id, $imageFile = null)
    {
        $certificate = CertificateReplacement::findOrFail($id);
        $this->authorize('update', $certificate);

        if ($imageFile) {
            $data['student_image'] = $this->uploadFile(
                $imageFile,
                'certificate_replacements/students-images',
                'public',
                $certificate->student_image
            );
        }

        $certificate->update($data);

        $certificate->load('student');
        $this->activityLogService->logAction(
            'certificate_replacements',
            $certificate,
            'update',
            "تم تعديل بيانات الشهادة المصدرة للطالب: {$certificate->student->full_name}"
        );

        return $certificate;
    }

    public function deleteCertificate($id)
    {
        $certificate = CertificateReplacement::findOrFail($id);
        $this->authorize('delete', $certificate);
        
        // Optionally delete the image file if it exists
        if ($certificate->student_image) {
            // $this->deleteFile($certificate->student_image); // If trait has deleteFile
        }

        $name = $certificate->student->full_name ?? 'غير معروف';
        $certificate->delete();

        $this->activityLogService->logAction(
            'certificate_replacements',
            $certificate,
            'delete',
            "تم إلغاء سجل الشهادة للطالب: {$name}"
        );

        return $certificate;
    }
}
