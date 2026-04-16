<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_override' => ['nullable', 'in:full,three_quarter,half,quarter,zero'],
        ];
    }
}
