<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateLandingPageRequest extends BaseCompanyRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->hasFile('thumbnail')) {
            $file = $this->file('thumbnail');
            $mime = $file->getMimeType() ?? '';
            $ext = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];
            if (str_starts_with($mime, 'video/') || in_array($ext, $videoExtensions)) {
                $this->files->set('video', $file);
                $this->files->remove('thumbnail');
            }
        }
    }

    public function rules(): array
    {

        return array_merge(
            $this->companyRules(),
            [
                'product_id' => ['nullable', 'exists:products,id'],
                'product_ids' => ['nullable', 'array'],
                'product_ids.*' => ['integer', 'exists:products,id'],
                'name' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'template_id' => ['nullable'],
                'domain' => [
                    'nullable',
                    'string',

                ],
                'short_description' => ['nullable', 'string'],
                'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'video' => ['nullable', 'mimes:mp4,mov,avi,wmv,flv,mkv,webm', 'max:51200'],
                'remove_video' => ['nullable'],
                'description' => ['nullable', 'string'],
                'regular_price' => ['nullable', 'string', 'max:255'],
                'discount_price' => ['nullable', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255'],
                'pixel' => ['nullable', 'string', 'max:255'],
                'meta_access_token' => ['nullable', 'string'],
                'header_code' => ['nullable', 'string'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'instruction' => ['nullable', 'string'],
                'extras' => ['nullable', 'string'],
                'instruction_title' => ['nullable', 'string', 'max:255'],
                'img_paths.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'existing_img_paths' => ['nullable', 'array'],
                'existing_img_paths.*' => ['nullable', 'string'],
                'status' => ['nullable'],
                'meta_title' => ['nullable', 'string'],
                'meta_description' => ['nullable', 'string'],
                'meta_keywords' => ['nullable'],
                'meta_image' => ['nullable'],
                'remove_meta_image' => ['nullable'],
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
