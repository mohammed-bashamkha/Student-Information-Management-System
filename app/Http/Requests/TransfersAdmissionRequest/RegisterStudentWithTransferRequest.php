<?php

namespace App\Http\Requests\TransfersAdmissionRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentWithTransferRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'school_number' => 'required|string|max:50',
            'seat_number' => 'required|string|max:50',
            'gender' => 'required|in:male,female',
            'nationality' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string|max:100',
            'to_school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'from_external_school_name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:700',
        ];
    }
}
