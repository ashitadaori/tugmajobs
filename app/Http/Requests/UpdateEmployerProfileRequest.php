<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployerProfileRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string|max:5000',
            'company_website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'company_size' => 'nullable|string|in:1-10,11-50,51-200,201-500,501-1000,1000+',
            'industry' => 'nullable|string|max:100',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'contact_person_name' => 'nullable|string|max:100',
            'contact_person_designation' => 'nullable|string|max:100',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'company_video' => 'nullable|url|max:255',
            'hiring_process' => 'nullable|array',
            'company_culture' => 'nullable|array',
            'benefits_offered' => 'nullable|array',
            'specialties' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Company name is required.',
            'company_name.max' => 'Company name cannot exceed 255 characters.',
            'company_logo.image' => 'Company logo must be an image file.',
            'company_logo.max' => 'Company logo cannot exceed 2MB.',
            'company_website.url' => 'Please enter a valid website URL.',
            'business_email.email' => 'Please enter a valid email address.',
            'founded_year.min' => 'Please enter a valid founding year.',
            'founded_year.max' => 'Founding year cannot be in the future.',
        ];
    }
}
