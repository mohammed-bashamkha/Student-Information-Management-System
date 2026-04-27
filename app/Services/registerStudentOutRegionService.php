<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterStudentOutRegionService
{
    public function registerStudentWithTransfer(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $student = Student::create([
                'full_name'         => $data['full_name'],
                'school_number'     => $data['school_number'],
                'seat_number'       => $data['seat_number'],
                'gender'            => $data['gender'],
                'nationality'       => $data['nationality'],
                'date_of_birth'     => $data['date_of_birth'],
                'place_of_birth'    => $data['place_of_birth'],
                'registration_date' => now(),
                'created_by'        => Auth::id(),
            ]);

            $enrollment = StudentEnrollment::create([
                'student_id'       => $student->id,
                'school_id'        => $data['to_school_id'],
                'class_id'         => $data['class_id'],
                'academic_year_id' => $data['academic_year_id'],
                'status'           => 'active',
                'enrollment_date'  => now(),
                'created_by'       => Auth::id(),
            ]);

            $transfer = TransfersAdmission::create([
                'created_by'                => Auth::id(),
                'type'                      => 'transfer',
                'student_id'                => $student->id,
                'from_school_id'            => null,
                'from_external_school_name' => $data['from_external_school_name'],
                'to_school_id'              => $data['to_school_id'],
                'class_id'                  => $data['class_id'],
                'academic_year_id'          => $data['academic_year_id'],
                'request_date'              => now(),
                'approval_date'             => now(),
                'status'                    => 'approved',
                'reason'                    => $data['reason'] ?? null,
            ]);

            return [
                'student'    => $student,
                'enrollment' => $enrollment,
                'transfer'   => $transfer,
            ];
        });
    }
}