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

    public function bulkSaveGrades(array $data)
    {
        $this->authorize('create', Grade::class);

        $studentId = $data['student_id'];
        $academicYearId = $data['academic_year_id'];
        
        // Find enrollment to get school and class
        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->with('schoolClass.subjects')
            ->first();

        if (!$enrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'الطالب غير مسجل في هذا العام الدراسي'
            ]);
        }

        $classSubjectIds = $enrollment->schoolClass->subjects->pluck('id')->toArray();

        $userId = Auth::id();

        DB::transaction(function () use ($data, $enrollment, $userId, $studentId, $academicYearId, $classSubjectIds) {
            foreach ($data['grades'] as $gradeData) {
                // Ensure the subject actually belongs to the student's class
                if (!in_array($gradeData['subject_id'], $classSubjectIds)) {
                    continue;
                }

                $existingGrade = Grade::where([
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'subject_id' => $gradeData['subject_id'],
                ])->first();

                // Safely extract values or preserve existing ones if omitted
                $first = array_key_exists('first_semester', $gradeData) ? $gradeData['first_semester'] : ($existingGrade ? $existingGrade->first_semester_total : null);
                $second = array_key_exists('second_semester', $gradeData) ? $gradeData['second_semester'] : ($existingGrade ? $existingGrade->second_semester_total : null);

                // Skip if both are null and no existing grade
                if ($first === null && $second === null && !$existingGrade) {
                    continue;
                }

                $total = null;
                if ($first !== null || $second !== null) {
                    $total = (float)($first ?? 0) + (float)($second ?? 0);
                }

                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'academic_year_id' => $academicYearId,
                        'subject_id' => $gradeData['subject_id'],
                    ],
                    [
                        'school_id' => $enrollment->school_id,
                        'class_id' => $enrollment->class_id,
                        'first_semester_total' => $first,
                        'second_semester_total' => $second,
                        'total' => $total,
                        'created_by' => $userId,
                    ]
                );
            }

            // Calculate final result ONCE at the end
            $this->resultCalculationService->calculateFinalResult($studentId, $academicYearId);
        });

        // Log the bulk action
        $studentName = $enrollment->student->full_name ?? 'طالب';
        $this->activityLogService->logAction(
            'grades',
            null,
            'create', // Assuming this covers update too conceptually
            "تم رصد مجموعة درجات للطالب: {$studentName} للعام الدراسي المختار"
        );

        return Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->get();
    }

    public function deleteGradesByStudentAndYear(string $studentId, string $academicYearId)
    {
        $this->authorize('deleteAny', Grade::class);

        DB::transaction(function () use ($studentId, $academicYearId) {
            Grade::where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->delete();

            // Re-calculate final result (which might clear it or mark as missing)
            $this->resultCalculationService->calculateFinalResult($studentId, $academicYearId);
        });

        $this->activityLogService->logAction(
            'grades',
            null,
            'delete',
            "تم حذف جميع درجات الطالب (ID: {$studentId}) للعام الدراسي (ID: {$academicYearId})"
        );
    }
}
