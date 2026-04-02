<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FinalResultImportRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:school_classes,id',
            'school_id' => 'required|exists:schools,id',
        ];
        
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف Excel',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel (xlsx, xls)',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'academic_year_id.required' => 'يرجى اختيار السنة الدراسية',
            'class_id.required' => 'يرجى اختيار الصف',
            'school_id.required' => 'يرجى اختيار المدرسة',
        ];
    }
}
