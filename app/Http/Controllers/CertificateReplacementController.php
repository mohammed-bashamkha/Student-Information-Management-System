<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificateReplacementRequest\StoreCertificateReplacementReuest;
use App\Http\Requests\CertificateReplacementRequest\UpdateCertificateReplacementReuest;
use App\Models\CertificateReplacement;
use App\Traits\UploadFileTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateReplacementController extends Controller
{
    use AuthorizesRequests;
    use UploadFileTrait;
    public function index(Request $request)
    {
        $this->authorize('viewAny', CertificateReplacement::class);

        $query = CertificateReplacement::with(['student', 'school', 'schoolClass', 'academicYear', 'createdBy']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('seat_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        $certificates = $query->latest()->paginate(15);

        return response()->json($certificates, 200);
    }

    public function store(StoreCertificateReplacementReuest $request)
    {
        $this->authorize('create', CertificateReplacement::class);

        $data = $request->validated();

        $data['created_by'] = Auth::id();

        if ($request->hasFile('student_image')) {
            $data['student_image'] = $this->uploadFile($request->file('student_image'), 'certificate_replacements/students-images');
        }

        $certificate = CertificateReplacement::create($data);

        return response()->json([
            'message' => 'تم تسجيل إصدار الشهادة بنجاح',
            'data'    => $certificate->load(['student', 'school', 'academicYear'])
        ], 201);
    }

    public function show($id)
    {
        $certificate = CertificateReplacement::with(['student', 'school', 'academicYear', 'user'])->findOrFail($id);
        $this->authorize('view', $certificate);

        return response()->json(['data' => $certificate], 200);
    }

    public function update(UpdateCertificateReplacementReuest $request, $id)
    {
        $certificate = CertificateReplacement::findOrFail($id);
        $this->authorize('update', $certificate);

        $data = $request->validated();

        if ($request->hasFile('student_image')) {
            $data['student_image'] = $this->uploadFile(
                $request->file('student_image'),
                'certificate_replacements/students-images',
                'public',
                $certificate->student_image
            );
        }

        $certificate->update($data);

        return response()->json([
            'message' => 'تم تعديل بيانات الشهادة المصدرة بنجاح',
            'data'    => $certificate
        ], 200);
    }

    public function destroy($id)
    {
        $certificate = CertificateReplacement::findOrFail($id);
        $this->authorize('delete', $certificate);
        $certificate->delete();

        return response()->json([
            'message' => 'تم إلغاء سجل الشهادة بنجاح'
        ], 200);
    }
}
