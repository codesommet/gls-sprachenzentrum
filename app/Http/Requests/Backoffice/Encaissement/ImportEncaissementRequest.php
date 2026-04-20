<?php

namespace App\Http\Requests\Backoffice\Encaissement;

use Illuminate\Foundation\Http\FormRequest;

class ImportEncaissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id'       => 'required|exists:sites,id',
            'source_system' => 'required|in:old_crm,new_crm',
            'school_year'   => ['nullable', 'string', 'max:20', 'regex:#^\d{4}/\d{4}$#'],
            'month'         => ['required', 'string', 'max:7', 'regex:#^\d{4}-(0[1-9]|1[0-2])$#'],
            'file'          => 'required|file|max:20480',
            'notes'         => 'nullable|string|max:2000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');
            if ($file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls', 'csv', 'pdf'])) {
                    $validator->errors()->add('file', 'Le fichier doit être Excel (.xlsx, .xls, .csv) ou PDF.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'Veuillez sélectionner un centre.',
            'source_system.required' => 'Veuillez sélectionner le type de CRM.',
            'file.required' => 'Veuillez uploader un fichier.',
            'file.mimes' => 'Le fichier doit être Excel (.xlsx, .xls, .csv) ou PDF.',
            'file.max' => 'Le fichier ne doit pas dépasser 20 Mo.',
            'school_year.regex' => 'Format année scolaire invalide (ex: 2025/2026).',
            'month.required' => 'Veuillez sélectionner le mois.',
            'month.regex' => 'Format mois invalide.',
        ];
    }
}
