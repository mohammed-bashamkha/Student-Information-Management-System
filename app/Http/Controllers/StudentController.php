<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest\StoreStudentWithEnrollmentRequest;
use App\Http\Requests\StudentRequest\UpdateStudentWithEnrollmentRequest;
use App\Models\Error;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $query = Student::query();

        if ($request->filled('academic_year_id')) {
            $query->with(['enrollments' => function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id)
                ->with(['school', 'schoolClass']);
            }]);
        } else {
            $query->with(['currentEnrollment.school', 'currentEnrollment.schoolClass']);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('school_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('seat_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('nationality', 'LIKE', "%{$searchTerm}%")
                ->orWhere('gender', 'LIKE', "%{$searchTerm}%")
                ->orWhere('date_of_birth', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('academic_year_id') || $request->filled('school_id') || $request->filled('class_id')) {
            
            $query->whereHas('enrollments', function ($q) use ($request) {
                
                if ($request->filled('academic_year_id')) {
                    $q->where('academic_year_id', $request->academic_year_id);
                }

                if ($request->filled('school_id')) {
                    $q->where('school_id', $request->school_id);
                }

                if ($request->filled('class_id')) {
                    $q->where('class_id', $request->class_id);
                }
                
            });
        }

        $students = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json($students, 200);
    }

    public function store(StoreStudentWithEnrollmentRequest $request)
    {
        $this->authorize('create',Student::class);

        $data = $request->validated();

         $result = DB::transaction(function () use ($data, $request) {
            $student = Student::create([
                'school_number' => $data['school_number'],
                'seat_number' => $data['seat_number'],
                'full_name' => $data['full_name'],
                'nationality' => $data['nationality'] ?? null,
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'registration_date' => $data['registration_date'] ?? null,
                'created_by' => Auth::id()
            ]);
            $enrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $request->school_id,
                'class_id' => $request->class_id,
                'academic_year_id' => $request->academic_year_id,
                'created_by' => Auth::id()
            ]);

            $enrollment->load(['school', 'schoolClass']);

            return [
                'student' => $student,
                'enrollment' => $enrollment
            ];
        });

        return response()->json([
            'message' => 'تم اضافة الطالب بنجاح',
            'data' => $result['student'],
            'school_enrollment' => $result['enrollment']->school->name,
            'class_enrollment' => $result['enrollment']->schoolClass->name
        ], 201);
    }

    public function show(string $id)
    {
        $student = Student::with(['enrollments.school', 'enrollments.schoolClass', 'enrollments.academicYear'])->findOrFail($id);

        return response()->json([
            'message' => 'تم جلب بيانات الطالب بنجاح',
            'data' => $student
        ], 200);
    }

    public function update(UpdateStudentWithEnrollmentRequest $request, $id)
    {
    $student = Student::findOrFail($id);
    $this->authorize('update', $student);

    $data = $request->validated();
    $data['school_number'] = ['required', 'integer', 'min_digits:3', Rule::unique('students', 'school_number')->ignore($student->id)];
    $data['seat_number'] = ['required', 'integer', 'min_digits:3', Rule::unique('students', 'seat_number')->ignore($student->id)];
    $data['full_name'] = ['required', 'string', Rule::unique('students', 'full_name')->ignore($student->id)];

    DB::transaction(function () use ($student, $request) {


        $fieldsToTrack = [
            'full_name', 'school_number', 'seat_number', 
            'gender', 'school_id', 'class_id', 'date_of_birth'];

        foreach ($fieldsToTrack as $field) 
        {
            if ($request->has($field) && $request->input($field) != $student->$field) {
                Error::create([
                    'student_id'       => $student->id,
                    'field_name'       => $field,                    // اسم الحقل (مثلاً: full_name)
                    'old_value'        => $student->$field,          // الاسم الخطأ (القديم)
                    'new_value'        => $request->input($field), 
                    'academic_year_id' => $request->input('academic_year_id'),
                    'reason'           => $request->input('reason'),
                    'school_id'        => $request->input('school_id'),
                    'class_id'        => $request->input('class_id'),
                    'createdBy'          => Auth::id(),      
                ]);
            }
        }

        $student->update($request->only([
            'school_number', 'seat_number', 'full_name', 'nationality',
            'gender', 'date_of_birth', 'registration_date'
        ]));

        StudentEnrollment::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $request->academic_year_id,
            ],
            [
                'school_id' => $request->school_id,
                'class_id' => $request->class_id,
                'created_by' => Auth::id()
            ]
        );

    });

        $student->load(['enrollments' => function($query) use ($request) {
            $query->where('academic_year_id', $request->academic_year_id)
                ->with(['school', 'schoolClass']);
        }]);

        return response()->json([
            'message' => 'تم تعديل بيانات الطالب والتسجيل بنجاح',
            'data' => $student
        ], 200);
    }

    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'message' => 'تم حذف الطالب بنجاح',
            'data' => $student->only('school_number', 'full_name')
        ], 200);
    }
}
