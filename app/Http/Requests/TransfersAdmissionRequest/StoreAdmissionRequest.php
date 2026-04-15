<?php

namespace App\Http\Requests\TransfersAdmissionRequest;

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
            'to_school_id'     => 'required|exists:schools,id',
            'request_date'     => 'nullable|date',
            'reason'           => 'nullable|string',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'based_on'         => 'nullable|string',
        ];
    }
}
