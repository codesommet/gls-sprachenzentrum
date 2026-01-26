<?php

namespace App\Http\Requests\Backoffice\Studienkolleg;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudienkollegRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'university' => ['nullable', 'string', 'max:255'],

            'hero_image' => ['nullable', 'image', 'max:4096'],
            'card_image' => ['nullable', 'image', 'max:4096'],
            'university_logo' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'string'],

            'featured' => ['sometimes', 'boolean'],
            'public' => ['sometimes', 'boolean'],
            'uni_assist' => ['sometimes', 'boolean'],
            'entrance_exam' => ['sometimes', 'boolean'],
            'certification_required' => ['sometimes', 'boolean'],
            'translation_required' => ['sometimes', 'boolean'],

            'duration_semesters' => ['required', 'integer', 'min:1'],
            'tuition' => ['required', 'string', 'max:50'],
            'language_of_instruction' => ['required', 'string', 'max:50'],

            'courses' => ['nullable', 'array'],
            'courses.*' => ['string', 'max:50'],

            'languages' => ['nullable', 'string'],
            'documents' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],

            'deadlines' => ['nullable', 'array'],
            'deadlines.Winter Semester' => ['nullable', 'array'],
            'deadlines.Winter Semester.range' => ['nullable', 'string', 'max:100'],
            'deadlines.Winter Semester.note' => ['nullable', 'string'],
            'deadlines.Summer Semester' => ['nullable', 'array'],
            'deadlines.Summer Semester.range' => ['nullable', 'string', 'max:100'],
            'deadlines.Summer Semester.note' => ['nullable', 'string'],

            'application_method' => ['nullable', 'string', 'max:255'],
            'application_portal_note' => ['nullable', 'string', 'max:255'],
            'application_url' => ['nullable', 'url'],
            'exam_subjects' => ['nullable', 'string', 'max:255'],
            'exam_link' => ['nullable', 'url'],
            'exam_url' => ['nullable', 'url'],
            'translation_note' => ['nullable', 'string', 'max:255'],

            'official_website' => ['nullable', 'url'],
            'contact_email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'map_embed' => ['nullable', 'string'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ];
    }
}
