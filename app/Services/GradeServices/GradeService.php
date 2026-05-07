<?php

namespace App\Services\GradeServices;

use App\Models\Grade;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Services\ActivityLogServices\ActivityLogService;
use App\Services\ResultCalculationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeService
{
    use AuthorizesRequests;
    protected $resultCalculationService;
    protected $activityLogService;

    public function __construct(ResultCalculationService $resultCalculationService, ActivityLogService $activityLogService)
    {
        $this->resultCalculationService = $resultCalculationService;
        $this->activityLogService = $activityLogService;
    }

    public function getGrades()
    {
        $this->authorize('viewAny', Grade::class);
        $grades = Grade::with(['student', 'subject', 'academicYear'])->get();
        return $grades;
    }

    public function storeGrade(array $validateData)
    {
        $this->authorize('create', Grade::class);
        
        $enrollment = $this->validateEnrollment($validateData);

        $totalSemesteres = $validateData['first_semester_total'] + $validateData['second_semester_total'];
        $validateData['total']      = $totalSemesteres;
        $validateData['created_by'] = Auth::id();
        $validateData['school_id']  = $enrollment->school_id;
        $validateData['class_id']   = $enrollment->class_id;
        $validateData['academic_year_id'] = $enrollment->academic_year_id;

        $grade = DB::transaction(function () use ($validateData) {
            $grade = Grade::create($validateData);

            $this->resultCalculationService->calculateFinalResult(
                $validateData['student_id'],
                $validateData['academic_year_id']
            );

            return $grade;
        });

        $grade->load(['student', 'subject']);
        $this->activityLogService->logAction(
            'grades',
            $grade,
            'create',
            "تم إضافة درجة للطالب: {$grade->student->full_name} في مقرر: {$grade->subject->name}"
        );

        return $grade;
    }

    public function getGradeById(string $id)
    {
        $grade = Grade::with(['student', 'subject', 'academicYear'])->findOrFail($id);
        $this->authorize('view', $grade);
        return $grade;
    }

    public function updateGrade(array $validateData, string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('update', $grade);

        $this->validateEnrollment($validateData);

        if (
            $grade->student_id != $validateData['student_id'] ||
            $grade->subject_id != $validateData['subject_id'] ||
            $grade->academic_year_id != $validateData['academic_year_id']
        ) {
            throw ValidationException::withMessages([
                'student_id' => 'لا يمكن تغيير الطالب أو المقرر أو العام الدراسي لهذه الدرجة'
            ]);
        }

        $totalSemesteres = $validateData['first_semester_total'] + $validateData['second_semester_total'];
        $validateData['total'] = $totalSemesteres;

        $grade = DB::transaction(function () use ($grade, $validateData) {
            $grade->update($validateData);
            $this->resultCalculationService->calculateFinalResult(
                $grade->student_id,
                $grade->academic_year_id
            );
            return $grade;
        });

        $grade->load(['student', 'subject']);
        $this->activityLogService->logAction(
            'grades',
            $grade,
            'update',
            "تم تعديل درجة الطالب: {$grade->student->full_name} في مقرر: {$grade->subject->name}"
        );

        return $grade;
    }

    private function validateEnrollment(array $data)
    {
        $subject = Subject::find($data['subject_id']);

        if (!$subject) {
            throw ValidationException::withMessages([
                'subject_id' => 'المقرر الدراسي غير موجود'
            ]);
        }

        $enrollment = StudentEnrollment::where('student_id', $data['student_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->with('schoolClass.subjects')
            ->first();

        if (!$enrollment || !$enrollment->schoolClass->subjects->contains($subject)) {
            throw ValidationException::withMessages([
                'subject_id' => "الطالب غير مسجل في مقرر ({$subject->name}) لهذا العام الدراسي"
            ]);
        }

        return $enrollment;
    }

    public function deleteGrade(string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('delete', $grade);

        $studentId      = $grade->student_id;
        $academicYearId = $grade->academic_year_id;

        DB::transaction(function () use ($grade, $studentId, $academicYearId) {
            $grade->delete();
            $this->resultCalculationService->calculateFinalResult($studentId, $academicYearId);
        });

        $this->activityLogService->logAction(
            'grades',
            $grade,
            'delete',
            "تم حذف درجة الطالب: {$grade->student->full_name} في مقرر: {$grade->subject->name}"
        );

        return $grade;
    }
}
