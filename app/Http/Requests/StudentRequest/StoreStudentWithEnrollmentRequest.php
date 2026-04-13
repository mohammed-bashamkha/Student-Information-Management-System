<?php

namespace App\Http\Requests\StudentRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentWithEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_number' => 'required|integer|min_digits:3|unique:students,school_number',
            'seat_number' => 'required|integer',
            'full_name' => ['required', 'string', 'regex:/^(\S+\s){3,}\S+$/'],
            'nationality' => 'string|nullable|max:15',
            'gender' => 'required|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'registration_date' => 'nullable|date',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id'
        ];
    }
}
