<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMarketRequest extends BaseCompanyRequest
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
                'domain_verify'         => 'sometimes|nullable|string',
                'facebook_pixel_id'     => 'sometimes|nullable|string',
                'tiktok_pixel_id'       => 'sometimes|nullable|string',
                'tiktok_access_token'   => 'sometimes|nullable|string',
                'meta_access_token'     => 'sometimes|nullable|string',
                'google_tag_id'         => 'sometimes|nullable|string',
                'google_measurement_id' => 'sometimes|nullable|string',
            ]
        );
    }
    /**
     * Custom error messages (Optional)
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'facebook_pixel_id.string' => 'The Facebook Pixel ID must be a valid string.',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
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
