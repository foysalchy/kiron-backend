<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSliderRequest extends BaseCompanyRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'title'       => ['required', 'string', 'max:255'],
                'subtitle'    => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'url' => ['nullable', 'url'],
                'placement' => ['required', 'string'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'status'      => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required' => 'Slider title is required.',
                'image.required' => 'A slider image is required.',
                'image.image'    => 'The file must be an image.',
                'image.max'      => 'The image size cannot exceed 2MB.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
