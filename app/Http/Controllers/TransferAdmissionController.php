<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransfersAdmissionRequest\RegisterStudentWithTransferRequest;
use App\Http\Requests\TransfersAdmissionRequest\StoreAdmissionRequest;
use App\Http\Requests\TransfersAdmissionRequest\StoreTransferRequest;
use App\Http\Requests\TransfersAdmissionRequest\UpdateTransfersAdmissionRequest;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use App\Services\RegisterStudentOutRegionService;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferAdmissionController extends Controller
{
    use AuthorizesRequests;
    protected $registerStudentOutRegionService;
    public function __construct(RegisterStudentOutRegionService $registerStudentOutRegionService)
    {
        $this->registerStudentOutRegionService = $registerStudentOutRegionService;
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', TransfersAdmission::class);
        $query = TransfersAdmission::with(['student', 'fromSchool', 'toSchool', 'academicYear', 'user']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('to_school_id')) {
            $query->where('to_school_id', $request->to_school_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $transfers = $query->latest()->paginate(15);

        return response()->json($transfers, 200);
    }

    public function storeTransfer(StoreTransferRequest $request)
    {
        $this->authorize('create', TransfersAdmission::class);

        $data = $request->validated();

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

            // إذا تمت الموافقة مباشرةً عند الإنشاء
            if ($status === 'approved') {
                $this->applyEnrollment($transfer);
            }

            return $transfer;
        });

        $message = $status === 'approved'
            ? 'تم تسجيل طلب النقل وتمت الموافقة عليه مباشرةً'
            : 'تم تسجيل طلب النقل بنجاح وهو الآن قيد الانتظار';

        activity('transfers')
            ->causedBy(Auth::user())
            ->performedOn($transfer)
            ->event('create')
            ->log('تم إنشاء طلب نقل للطالب: ' . $student->full_name);

        return response()->json([
            'message' => $message,
            'data'    => $transfer->load(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ], 201);
    }

    public function storeAdmission(StoreAdmissionRequest $request)
    {
        $this->authorize('create', TransfersAdmission::class);

        $data = $request->validated();

        $student = Student::with('currentEnrollment')->findOrFail($data['student_id']);

        // التأكد من عدم وجود طلب قبول مؤقت مكرر
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

            // إذا تمت الموافقة مباشرةً عند الإنشاء
            if ($status === 'approved') {
                $this->applyEnrollment($admission);
            }

            return $admission;
        });

        $message = $status === 'approved'
            ? 'تم تسجيل طلب القبول المؤقت وتمت الموافقة عليه مباشرةً'
            : 'تم تسجيل طلب القبول المؤقت بنجاح وهو الآن قيد الانتظار';

        activity('admissions')
            ->causedBy(Auth::user())
            ->performedOn($admission)
            ->event('create')
            ->log('تم إنشاء طلب قبول مؤقت للطالب: ' . $student->full_name);

        return response()->json([
            'message' => $message,
            'data'    => $admission->load(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ], 201);
    }

    public function update(UpdateTransfersAdmissionRequest $request, $id)
    {
        $transferAdmission = TransfersAdmission::findOrFail($id);
        $this->authorize('update', $transferAdmission);

        $data = $request->validated();
        $newStatus = $data['status'];
        $currentStatus = $transferAdmission->status;

        // ===== حماية انتقال الحالة =====
        $allowedTransitions = [
            'pending'  => ['approved', 'rejected'],
            'rejected' => ['pending', 'approved'],
            'approved' => ['rejected'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "لا يمكن تغيير الحالة من \"{$currentStatus}\" إلى \"{$newStatus}\""
            ], 422);
        }

        // ===== تنفيذ التحديث داخل Transaction =====
        DB::transaction(function () use ($transferAdmission, $data, $newStatus) {

            $transferAdmission->update([
                'status'        => $newStatus,
                'approval_date' => $newStatus === 'approved' ? ($data['approval_date'] ?? now()) : null,
                'reason'        => $data['reason'] ?? $transferAdmission->reason,
            ]);

            // عند القبول: تحديث أو إنشاء تسجيل الطالب في المدرسة الجديدة
            if ($newStatus === 'approved') {
                $this->applyEnrollment($transferAdmission);
            }
        });

        $statusMessages = [
            'approved' => 'تم قبول الطلب بنجاح وتم تحديث بيانات الطالب',
            'rejected' => 'تم رفض الطلب',
            'pending'  => 'تم إعادة فتح الطلب وهو الآن قيد الانتظار',
        ];

        activity('transfers')
            ->causedBy(Auth::user())
            ->performedOn($transferAdmission)
            ->event($newStatus)
            ->log("تغيير حالة الطلب من '{$currentStatus}' إلى '{$newStatus}'");

        return response()->json([
            'message' => $statusMessages[$newStatus],
            'data'    => $transferAdmission->fresh(['student', 'fromSchool', 'toSchool', 'schoolClass', 'academicYear'])
        ], 200);
    }

    public function show($id)
    {
        $transfer = TransfersAdmission::with(['student', 'fromSchool', 'toSchool', 'academicYear', 'user'])->findOrFail($id);
        $this->authorize('view', $transfer);
        return response()->json($transfer, 200);
    }

    public function destroy($id)
    {
        $transfer = TransfersAdmission::findOrFail($id);
        $this->authorize('delete', $transfer);
        if ($transfer->status === 'approved') {
            return response()->json(['message' => 'لا يمكن حذف طلب تم قبوله وتأكيده مسبقاً'], 400);
        }

        $transfer->delete();

        activity('transfers')
            ->causedBy(Auth::user())
            ->event('delete')
            ->log('تم حذف طلب ' . ($transfer->type === 'transfer' ? 'نقل' : 'قبول مؤقت'));

        return response()->json(['message' => 'تم حذف الطلب بنجاح'], 200);
    }

    // ========== Private Helpers ==========

    /**
     * تحديث أو إنشاء تسجيل الطالب عند موافقة طلب تحويل أو قبول مؤقت
     */
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
