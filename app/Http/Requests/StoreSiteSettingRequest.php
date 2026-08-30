<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSiteSettingRequest extends BaseCompanyRequest
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
                'shop_name'         => ['required', 'string', 'max:255'],
                'title'             => ['required', 'string', 'max:255'],
                'description'       => ['nullable', 'string'],
                'logo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'favicon'           => ['nullable', 'mimes:png,ico,svg,webp', 'max:512'],
                'phone'             => ['required', 'string'],
                'alt_phone' => ['nullable', 'string'],
                'email'             => ['required', 'email'],
                'lang'             => ['nullable'],
                'currency'             => ['nullable'],
                'currency_position'             => ['nullable'],
                'country'             => ['nullable'],
                'inside_charge'             => ['nullable'],
                'outside_charge'             => ['nullable'],
                'corporate_address' => ['nullable', 'string'],
                'store_address'     => ['nullable', 'string'],
                'copy_right'     => ['nullable', 'string'],
                'tags'              => ['nullable', 'string'],
                'manage_warehouse'     => ['nullable'],
                'meta_image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // SEO ইমেজ ২ এমবি-র নিচে
                'founder_name'        => ['nullable', 'string', 'max:255'],
                'founder_designation' => ['nullable', 'string', 'max:255'],
                'established'         => ['nullable', 'date'],
            ]
        );
    }
    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'shop_name.required' => 'The shop name is mandatory.',
                'title.required'     => 'Site title is required for SEO.',
                'logo.logo'         => 'Logo must be an logo file.',
                'favicon.max'        => 'Favicon size should be under 512KB.',
                'phone.required'     => 'Primary phone number is required.',
                'email.email'        => 'Please provide a valid email address.',
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
