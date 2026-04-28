<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreLandingPageRequest extends BaseCompanyRequest
{
    public function rules(): array
    {

        return array_merge(
            $this->companyRules(),
            [
                'product_id' => ['nullable', 'exists:products,id'],
                'name' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'domain' => ['nullable','string'],
                'short_description' => ['nullable', 'string'],
                'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'video' => ['nullable', 'mimes:mp4,mov,avi,wmv,flv,mkv', 'max:51200'],
                'description' => ['nullable', 'string'],
                'pricing' => ['nullable', 'string'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:landing_pages,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                'pixel' => ['nullable', 'string', 'max:255'],
                'meta_access_token' => ['nullable', 'string'],
                'header_code' => ['nullable', 'string'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'instruction' => ['nullable', 'string'],
                'extras' => ['nullable'],
                'instruction_title' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Landing page name is required.',
                'title.required' => 'Landing page title is required.',
                'thumbnail.image' => 'Thumbnail must be an image file.',
                'thumbnail.max' => 'Thumbnail size cannot exceed 5MB.',
                'video.mimes' => 'Video must be a valid video file.',
                'video.max' => 'Video size cannot exceed 50MB.',
                'pricing.numeric' => 'Pricing must be a valid number.',
                'pricing.min' => 'Pricing cannot be negative.',
                'slug.unique' => 'This slug is already taken.',
                'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
                'template_id.exists' => 'Selected template does not exist.',
                'product_id.exists' => 'Selected product does not exist.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
