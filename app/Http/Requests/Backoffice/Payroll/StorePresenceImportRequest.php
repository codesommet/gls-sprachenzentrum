<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePresenceImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id'            => ['required', 'exists:groups,id'],
            'month'               => ['required', 'date_format:Y-m'],
            'payment_per_student' => ['nullable', 'numeric', 'min:0'],
            'file'                => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required' => 'Veuillez sélectionner un groupe.',
            'month.required'    => 'Veuillez indiquer le mois.',
            'file.required'     => 'Veuillez télécharger un fichier Excel.',
            'file.mimes'        => 'Le fichier doit être au format .xlsx, .xls ou .csv.',
            'file.max'          => 'Le fichier ne doit pas dépasser 10 Mo.',
        ];
    }
}
