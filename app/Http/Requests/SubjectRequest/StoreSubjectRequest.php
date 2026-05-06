<?php

namespace App\Http\Requests\SubjectRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
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
            'level_id' => 'integer|required|exists:levels,id',
            'name' => 'required|string|max:50|unique:subjects,name',
            'school_class_id' => 'array|nullable',
            'school_class_id.*' => 'required|exists:school_classes,id|min:1'
        ];
    }
}
