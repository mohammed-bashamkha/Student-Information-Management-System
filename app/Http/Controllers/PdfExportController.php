<?php

namespace App\Http\Controllers;

use App\Models\CertificateReplacement;
use App\Models\FinalResult;
use App\Models\Grade;
use App\Models\TransfersAdmission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class PdfExportController extends Controller
{
    use AuthorizesRequests;
    public function certificateReplacement(Request $request, $id)
    {
        $certificate = CertificateReplacement::with([
            'student',
            'school',
            'schoolClass',
            'academicYear',
            'createdBy',
        ])->findOrFail($id);

        $this->authorize('view', $certificate);

        // منع تصدير PDF للطالب الموقوف
        if ($certificate->student?->isSuspended()) {
            return response()->json(['message' => 'لا يمكن تصدير بيانات طالب موقوف'], 403);
        }

        $type = $request->query('type', 'student'); // student | office

        if ($type === 'office') {
            $view     = 'PDF.office-certificate-replacement';
            $filename = 'بدل-فاقد-إدارة-' . $certificate->id . '.pdf';
        } else {
            $view     = 'PDF.student-certificate-replacement';
            $filename = 'بدل-فاقد-طالب-' . $certificate->id . '.pdf';
        }

        return Pdf::view($view, [
            'certificate'  => $certificate,
            'student'      => $certificate->student,
            'school'       => $certificate->school,
            'schoolClass'  => $certificate->schoolClass,
            'academicYear' => $certificate->academicYear,
            'createdBy'    => $certificate->createdBy,
            'printDate'    => now()->format('Y/m/d'),
        ])
            ->format('a4')
            ->name($filename)
            ->download();
    }
    public function transfer($id)
    {
        $transfer = TransfersAdmission::with([
            'student',
            'fromSchool',
            'toSchool',
            'schoolClass',
            'academicYear',
            'createdBy',
        ])->where('type', 'transfer')->findOrFail($id);

        $this->authorize('view', $transfer);

        // منع تصدير PDF للطالب الموقوف
        if ($transfer->student?->isSuspended()) {
            return response()->json(['message' => 'لا يمكن تصدير بيانات طالب موقوف'], 403);
        }

        if ($transfer->status !== 'approved') {
            return response()->json(['message' => 'لم يتم الموافقة على طلب تحويل الطالب'], 403);
        }

        $filename = 'تحويل-طالب-' . $transfer->id . '.pdf';

        return Pdf::view('PDF.student-transfer', [
            'transfer'     => $transfer,
            'student'      => $transfer->student,
            'fromSchool'   => $transfer->fromSchool,
            'toSchool'     => $transfer->toSchool,
            'schoolClass'  => $transfer->schoolClass,
            'academicYear' => $transfer->academicYear,
            'createdBy'    => $transfer->createdBy,
            'printDate'    => now()->format('Y/m/d'),
        ])
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function admission($id)
    {
        $admission = TransfersAdmission::with([
            'student',
            'fromSchool',
            'toSchool',
            'schoolClass',
            'academicYear',
            'createdBy',
        ])->where('type', 'admission')->findOrFail($id);

        $this->authorize('view', $admission);

        // منع تصدير PDF للطالب الموقوف
        if ($admission->student?->isSuspended()) {
            return response()->json(['message' => 'لا يمكن تصدير بيانات طالب موقوف'], 403);
        }

        if ($admission->status !== 'approved') {
            return response()->json(['message' => 'لم يتم الموافقة على طلب القبول المؤقت'], 403);
        }

        $filename = 'قبول-مؤقت-' . $admission->id . '.pdf';

        return Pdf::view('PDF.student-admission', [
            'admission'    => $admission,
            'student'      => $admission->student,
            'fromSchool'   => $admission->fromSchool,
            'toSchool'     => $admission->toSchool,
            'schoolClass'  => $admission->schoolClass,
            'academicYear' => $admission->academicYear,
            'createdBy'    => $admission->createdBy,
            'printDate'    => now()->format('Y/m/d'),
        ])
            ->format('a4')
            ->name($filename)
            ->download();
    }

    /**
     * تصدير النتيجة النهائية للطالب
     * GET /api/pdf/final-result/{id}
     */
    public function finalResult($id)
    {
        $finalResult = FinalResult::with([
            'student.grades.subject',
            'student.currentEnrollment.school',
            'student.currentEnrollment.schoolClass',
            'student.school',
            'student.schoolClass',
            'academicYear',
        ])->findOrFail($id);

        $student = $finalResult->student;

        // منع تصدير PDF للطالب الموقوف
        if ($student->isSuspended()) {
            return response()->json(['message' => 'لا يمكن تصدير النتيجة النهائية لطالب موقوف'], 403);
        }

        $enrollment = $student->currentEnrollment
            ?? $student->enrollments()->latest()->first();

        $school      = $enrollment?->school      ?? $student->school;
        $schoolClass = $enrollment?->schoolClass ?? $student->schoolClass;

        // تجميع الدرجات مع بيانات المادة
        $grades = $student->grades->mapWithKeys(function (Grade $grade) {
            return [$grade->subject_id => $grade];
        });

        $subjects = $schoolClass?->subjects()->orderBy('id')->get() ?? collect();

        $filename = 'نتيجة-نهائية-' . $student->full_name . '.pdf';

        return Pdf::view('PDF.student-final-result', [
            'finalResult'  => $finalResult,
            'student'      => $student,
            'school'       => $school,
            'schoolClass'  => $schoolClass,
            'academicYear' => $finalResult->academicYear,
            'subjects'     => $subjects,
            'grades'       => $grades,
            'printDate'    => now()->format('Y/m/d'),
        ])
            ->format('a4')
            ->name($filename)
            ->download();
    }
}
