<?php

namespace App\Http\Requests\AcademicYearRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
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
            'year' => "required|string|max:9|unique:academic_years,year",
            'start_date' => "required|string|date",
            'end_date' => "required|string|date",
            'status' => "nullable|in:active,inactive"
        ];
    }
}
