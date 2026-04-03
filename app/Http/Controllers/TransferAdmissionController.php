<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmissionRequest;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransfersAdmissionRequest;
use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferAdmissionController extends Controller
{
    use AuthorizesRequests;
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

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('type')) { $query->where('type', $request->type); }
        if ($request->filled('to_school_id')) { $query->where('to_school_id', $request->to_school_id); }
        if ($request->filled('academic_year_id')) { $query->where('academic_year_id', $request->academic_year_id); }

        $transfers = $query->latest()->paginate(15);

        return response()->json($transfers, 200);
    }

    public function storeTransfer(StoreTransferRequest $request)
    {
        $this->authorize('create', TransfersAdmission::class);

        $data = $request->validated();

        $existingRequest = TransfersAdmission::where('student_id', $data['student_id'])
            ->where('type', 'transfer')
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('from_school_id', $data['from_school_id'])
            ->where('to_school_id', $data['to_school_id'])
            ->where('class_id', $data['class_id'])
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

        $data['type'] = 'transfer';
        $data['created_by'] = Auth::id();

        $transfer = TransfersAdmission::create($data);

        return response()->json([
            'message' => 'تم تسجيل طلب النقل بنجاح وهو الآن قيد الانتظار',
            'data'    => $transfer
        ], 201);
    }

    public function storeAdmission(StoreAdmissionRequest $request)
{
    $this->authorize('create', TransfersAdmission::class);

    $data = $request->validated();

    $existingRequest = TransfersAdmission::where('student_id', $data['student_id'])
        ->where('type', 'admission')
        ->where('academic_year_id', $data['academic_year_id'])
        ->where('from_school_id', $data['from_school_id'])
        ->where('to_school_id', $data['to_school_id'])
        ->where('class_id', $data['class_id'])
        ->whereIn('status', ['pending', 'approved'])
        ->first();

    if ($existingRequest) {
        $statusAr = $existingRequest->status === 'pending' ? 'قيد الانتظار' : 'مقبول مسبقاً';
        throw ValidationException::withMessages([
            'message' => "عفواً، لا يمكن إرسال طلب قبول مؤقت. يوجد طلب قبول مؤقت لهذا الطالب مسبقاً",
            'status'  => $statusAr
        ]);
    }

    $data['type'] = 'admission';
    $data['created_by'] = Auth::id();

    $admission = TransfersAdmission::create($data);

    return response()->json([
        'message' => 'تم تسجيل طلب القبول بنجاح وهو الآن قيد الانتظار',
        'data'    => $admission
    ], 201);
}

    public function update(UpdateTransfersAdmissionRequest $request, $id)
    {
        $transferAdmission = TransfersAdmission::findOrFail($id);
        $this->authorize('update', $transferAdmission);

        $request->validated();

        DB::transaction(function () use ($transferAdmission, $request) {
            
            $transferAdmission->update([
                'status'        => $request->status,
                'approval_date' => $request->status === 'approved' ? ($request->approval_date ?? now()) : null,
                'reason'        => $request->reason ?? $transferAdmission->reason,
            ]);

            if ($request->status === 'approved') {
                $enrollment = StudentEnrollment::where('student_id', $transferAdmission->student_id)
                    ->where('academic_year_id', $transferAdmission->academic_year_id)
                    ->first();

                if ($enrollment) {
                    $enrollment->update([
                        'school_id' => $transferAdmission->to_school_id,
                        'class_id'  => $transferAdmission->class_id,
                    ]);
                } else {
                    StudentEnrollment::create([
                        'student_id'       => $transferAdmission->student_id,
                        'academic_year_id' => $transferAdmission->academic_year_id,
                        'school_id'        => $transferAdmission->to_school_id,
                        'class_id'         => $transferAdmission->class_id,
                        'created_by'       => Auth::id()
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data'    => $transferAdmission->fresh(['student', 'fromSchool', 'toSchool', 'schoolClass'])
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
        return response()->json(['message' => 'تم حذف الطلب بنجاح'], 200);
    }
}
