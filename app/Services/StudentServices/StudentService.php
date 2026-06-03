<?php

namespace App\Services\StudentServices;

use App\Models\Error;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\ActivityLogServices\ActivityLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentService
{
    use AuthorizesRequests;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getStudents(array $filters = [])
    {
        $this->authorize('viewAny', Student::class);
        $query = Student::query();

        $query->with(['currentEnrollment.school', 'currentEnrollment.schoolClass', 'currentEnrollment.academicYear']);

        if (!empty($filters['academic_year_id'])) {
            $query->with(['enrollments' => function ($q) use ($filters) {
                $q->where('academic_year_id', $filters['academic_year_id'])
                    ->with(['school', 'schoolClass']);
            }]);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('seat_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('nationality', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('gender', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('date_of_birth', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['academic_year_id']) || !empty($filters['school_id']) || !empty($filters['class_id']) || !empty($filters['status'])) {
            $query->whereHas('enrollments', function ($q) use ($filters) {
                if (!empty($filters['academic_year_id'])) {
                    $q->where('academic_year_id', $filters['academic_year_id']);
                }
                if (!empty($filters['school_id'])) {
                    $q->where('school_id', $filters['school_id']);
                }
                if (!empty($filters['class_id'])) {
                    $q->where('class_id', $filters['class_id']);
                }
                if (!empty($filters['status'])) {
                    $q->where('status', $filters['status']);
                }
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function storeStudent(array $validatedData)
    {
        $this->authorize('create', Student::class);

        $user_id = Auth::id();

        $result = DB::transaction(function () use ($validatedData, $user_id) {
            $student = Student::create([
                'school_number' => $validatedData['school_number'],
                'seat_number' => $validatedData['seat_number'],
                'full_name' => $validatedData['full_name'],
                'nationality' => $validatedData['nationality'] ?? null,
                'gender' => $validatedData['gender'],
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'registration_date' => $validatedData['registration_date'] ?? null,
                'created_by' => $user_id
            ]);

            $enrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $validatedData['school_id'],
                'class_id' => $validatedData['class_id'],
                'academic_year_id' => $validatedData['academic_year_id'],
                'created_by' => $user_id
            ]);

            $enrollment->load(['school', 'schoolClass']);

            return [
                'student' => $student,
                'enrollment' => $enrollment
            ];
        });

        $this->activityLogService->logAction(
            'students',
            $result['student'],
            'create',
            'تم إضافة الطالب: ' . $result['student']->full_name
        );

        return $result;
    }

    public function getStudentById($id)
    {
        $student = Student::with([
            'enrollments.school',
            'enrollments.schoolClass',
            'enrollments.academicYear',
            'enrollments.student',
            'currentEnrollment.school',
            'currentEnrollment.schoolClass',
            'currentEnrollment.academicYear',
            'currentEnrollment.student',
        ])->findOrFail($id);
        $this->authorize('view', $student);
        return $student;
    }

    public function updateStudent(array $validatedData, string $id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('update', $student);

        DB::transaction(function () use ($student, $validatedData) {
            $student->load('currentEnrollment');

            $fieldsToTrack = [
                'full_name',
                'school_number',
                'seat_number',
                'gender',
                'school_id',
                'class_id',
                'place_of_birth',
                'date_of_birth'
            ];

            foreach ($fieldsToTrack as $field) {
                $oldValue = $student->$field;
                $newValue = $validatedData[$field] ?? null;

                // Handle fields that belong to enrollment
                if ($field === 'school_id' || $field === 'class_id') {
                    $oldValue = $student->currentEnrollment?->$field;
                }

                // Check if the value has changed
                if ($newValue !== null && $newValue != $oldValue && $oldValue !== null) {
                    Error::create([
                        'student_id'       => $student->id,
                        'field_name'       => $field,
                        'old_value'        => $oldValue,
                        'new_value'        => $newValue,
                        'academic_year_id' => $validatedData['academic_year_id'],
                        'reason'           => $validatedData['reason'] ?? 'تعديل بيانات',
                        'school_id'        => $validatedData['school_id'],
                        'class_id'         => $validatedData['class_id'],
                        'createdBy'        => Auth::id(),
                    ]);
                }
            }

            $student->update(collect($validatedData)->only([
                'school_number',
                'seat_number',
                'full_name',
                'nationality',
                'gender',
                'date_of_birth',
                'place_of_birth',
                'registration_date'
            ])->toArray());

            if (isset($validatedData['academic_year_id'])) {
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $validatedData['academic_year_id'],
                    ],
                    [
                        'school_id' => $validatedData['school_id'],
                        'class_id' => $validatedData['class_id'],
                        'created_by' => Auth::id()
                    ]
                );
            }
        });

        $student->load(['enrollments' => function ($query) use ($validatedData) {
            $query->where('academic_year_id', $validatedData['academic_year_id'] ?? null)
                ->with(['school', 'schoolClass']);
        }]);

        $this->activityLogService->logAction(
            'students',
            $student,
            'update',
            'تم تعديل بيانات الطالب: ' . $student->full_name
        );

        return $student;
    }

    public function deleteStudent(string $id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('delete', $student);

        $name = $student->full_name;
        $student->delete();

        $this->activityLogService->logAction(
            'students',
            $student,
            'delete',
            'تم حذف الطالب: ' . $name
        );

        return $student;
    }
}
