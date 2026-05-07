<?php

namespace App\Services\TransferAdmissionServices;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use App\Services\ActivityLogServices\ActivityLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferAdmissionService
{
    use AuthorizesRequests;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getTransfersAdmissions(array $filters = [])
    {
        $this->authorize('viewAny', TransfersAdmission::class);
        $query = TransfersAdmission::with(['student', 'fromSchool', 'toSchool', 'academicYear', 'user']);

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['to_school_id'])) {
            $query->where('to_school_id', $filters['to_school_id']);
        }
        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query->latest()->paginate(15);
    }

    public function storeTransfer(array $data)
    {
        $this->authorize('create', TransfersAdmission::class);

        $student = Student::with('currentEnrollment')->findOrFail($data['student_id']);

        $existingRequest = TransfersAdmission::where('student_id', $student->id)
            ->where('type', 'transfer')
            ->where('academic_year_id', $student->currentEnrollment->academic_year_id)
            ->where('from_school_id', $student->currentEnrollment->school_id)
            ->where('to_school_id', $data['to_school_id'])
            ->where('class_id', $student->currentEnrollment->class_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            $statusAr = $existingRequest->status === 'pending' ? 'قيد الانتظار' : 'مقبول مسبقاً';
            throw ValidationException::withMessages([
                'message' => "عفواً، لا يمكن إرسال طلب النقل. قد تم عمله مسبقاً",
                'type'    => $existingRequest->type,
                'status'  => $statusAr
            ]);
        }

        $status = $data['status'] ?? 'pending';

        $transfer = DB::transaction(function () use ($data, $student, $status) {
            $transfer = TransfersAdmission::create([
                'student_id'       => $student->id,
                'academic_year_id' => $student->currentEnrollment->academic_year_id,
                'from_school_id'   => $student->currentEnrollment->school_id,
                'to_school_id'     => $data['to_school_id'],
                'request_date'     => $data['request_date'] ?? now(),
                'class_id'         => $student->currentEnrollment->class_id,
                'type'             => 'transfer',
                'status'           => $status,
                'approval_date'    => $status === 'approved' ? now() : null,
                'reason'           => $data['reason'] ?? null,
                'based_on'         => $data['based_on'] ?? null,
                'created_by'       => Auth::id(),
            ]);

            if ($status === 'approved') {
                $this->applyEnrollment($transfer);
            }

            return $transfer;
        });

        $this->activityLogService->logAction(
            'transfers',
            $transfer,
            'create',
            'تم إنشاء طلب نقل للطالب: ' . $student->full_name
        );

        return [
            'status' => $status,
            'transfer' => $transfer->load(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ];
    }

    public function storeAdmission(array $data)
    {
        $this->authorize('create', TransfersAdmission::class);

        $student = Student::with('currentEnrollment')->findOrFail($data['student_id']);

        $existingRequest = TransfersAdmission::where('student_id', $student->id)
            ->where('type', 'admission')
            ->where('academic_year_id', $student->currentEnrollment->academic_year_id)
            ->where('to_school_id', $data['to_school_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            $statusAr = $existingRequest->status === 'pending' ? 'قيد الانتظار' : 'مقبول مسبقاً';
            throw ValidationException::withMessages([
                'message' => "عفواً، لا يمكن إرسال طلب قبول مؤقت. يوجد طلب قبول مؤقت لهذا الطالب مسبقاً",
                'status'  => $statusAr
            ]);
        }

        $status = $data['status'] ?? 'pending';

        $admission = DB::transaction(function () use ($data, $student, $status) {
            $admission = TransfersAdmission::create([
                'student_id'       => $student->id,
                'academic_year_id' => $student->currentEnrollment->academic_year_id,
                'from_school_id'   => $student->currentEnrollment->school_id,
                'to_school_id'     => $data['to_school_id'],
                'class_id'         => $student->currentEnrollment->class_id,
                'type'             => 'admission',
                'status'           => $status,
                'approval_date'    => $status === 'approved' ? now() : null,
                'request_date'     => $data['request_date'] ?? now(),
                'start_date'       => $data['start_date'] ?? null,
                'end_date'         => $data['end_date'] ?? null,
                'reason'           => $data['reason'] ?? null,
                'based_on'         => $data['based_on'] ?? null,
                'created_by'       => Auth::id(),
            ]);

            if ($status === 'approved') {
                $this->applyEnrollment($admission);
            }

            return $admission;
        });

        $this->activityLogService->logAction(
            'admissions',
            $admission,
            'create',
            'تم إنشاء طلب قبول مؤقت للطالب: ' . $student->full_name
        );

        return [
            'status' => $status,
            'admission' => $admission->load(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ];
    }

    public function updateTransferAdmission(array $data, string $id)
    {
        $transferAdmission = TransfersAdmission::findOrFail($id);
        $this->authorize('update', $transferAdmission);

        $newStatus = $data['status'];
        $currentStatus = $transferAdmission->status;

        $allowedTransitions = [
            'pending'  => ['approved', 'rejected'],
            'rejected' => ['pending', 'approved'],
            'approved' => ['rejected'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            throw ValidationException::withMessages([
                'status' => "لا يمكن تغيير الحالة من \"{$currentStatus}\" إلى \"{$newStatus}\""
            ]);
        }

        DB::transaction(function () use ($transferAdmission, $data, $newStatus) {
            $transferAdmission->update([
                'status'        => $newStatus,
                'approval_date' => $newStatus === 'approved' ? ($data['approval_date'] ?? now()) : null,
                'reason'        => $data['reason'] ?? $transferAdmission->reason,
            ]);

            if ($newStatus === 'approved') {
                $this->applyEnrollment($transferAdmission);
            }
        });

        $this->activityLogService->logAction(
            'transfers',
            $transferAdmission,
            $newStatus,
            "تغيير حالة الطلب من '{$currentStatus}' إلى '{$newStatus}'"
        );

        return [
            'status' => $newStatus,
            'transferAdmission' => $transferAdmission->fresh(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ];
    }

    public function getTransferAdmissionById($id)
    {
        $transfer = TransfersAdmission::with(['student', 'fromSchool', 'toSchool', 'academicYear', 'user'])->findOrFail($id);
        $this->authorize('view', $transfer);
        return $transfer;
    }

    public function deleteTransferAdmission($id)
    {
        $transfer = TransfersAdmission::findOrFail($id);
        $this->authorize('delete', $transfer);
        
        if ($transfer->status === 'approved') {
            throw ValidationException::withMessages([
                'message' => 'لا يمكن حذف طلب تم قبوله وتأكيده مسبقاً'
            ]);
        }

        $type = $transfer->type;
        $transfer->delete();

        $this->activityLogService->logAction(
            'transfers',
            null,
            'delete',
            'تم حذف طلب ' . ($type === 'transfer' ? 'نقل' : 'قبول مؤقت')
        );

        return $transfer;
    }

    private function applyEnrollment(TransfersAdmission $record): void
    {
        $enrollment = StudentEnrollment::where('student_id', $record->student_id)
            ->where('academic_year_id', $record->academic_year_id)
            ->first();

        if ($enrollment) {
            $enrollment->update([
                'school_id' => $record->to_school_id,
                'class_id'  => $record->class_id,
            ]);
        } else {
            StudentEnrollment::create([
                'student_id'       => $record->student_id,
                'academic_year_id' => $record->academic_year_id,
                'school_id'        => $record->to_school_id,
                'class_id'         => $record->class_id,
                'created_by'       => Auth::id(),
            ]);
        }
    }
}
