<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isEmployer();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'qualifications' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'job_type_id' => 'required|exists:job_types,id',
            'vacancy' => 'required|integer|min:1|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'experience' => 'nullable|string|max:100',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,executive',
            'education_level' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_remote' => 'boolean',
            'deadline' => 'nullable|date|after:today',
            'preliminary_questions' => 'nullable|array|max:10',
            'preliminary_questions.*' => 'string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Job title is required.',
            'title.max' => 'Job title cannot exceed 255 characters.',
            'description.required' => 'Job description is required.',
            'description.min' => 'Job description must be at least 50 characters.',
            'category_id.required' => 'Please select a job category.',
            'category_id.exists' => 'The selected category is invalid.',
            'job_type_id.required' => 'Please select a job type.',
            'job_type_id.exists' => 'The selected job type is invalid.',
            'vacancy.required' => 'Number of vacancies is required.',
            'vacancy.min' => 'At least 1 vacancy is required.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'deadline.after' => 'Application deadline must be a future date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_remote' => $this->boolean('is_remote'),
        ]);
    }
}
