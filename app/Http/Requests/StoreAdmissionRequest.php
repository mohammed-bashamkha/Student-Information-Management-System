<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionRequest extends FormRequest
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
            'student_id'       => 'required|exists:students,id',
            'from_school_id'   => 'required|exists:schools,id',
            'to_school_id'     => 'required|exists:schools,id|different:from_school_id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:school_classes,id',
            'request_date'     => 'required|date',
            'reason'           => 'nullable|string',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'based_on'         => 'nullable|string',
        ];
    }
}
