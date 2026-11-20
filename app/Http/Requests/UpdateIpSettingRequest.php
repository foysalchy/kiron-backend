<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Models\IpSetting;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateIpSettingRequest extends UpdateBaseCompanyRequest
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
        IpSetting::ensureColumnsExist();

        return array_merge(
            $this->companyRules(),
            [
                'frequency_minute'        => ['sometimes', 'nullable', 'integer', 'min:1'],
                'order_limit'             => ['sometimes', 'nullable', 'integer', 'min:1'],
                'sms_limit_minutes'       => ['sometimes', 'nullable', 'integer', 'min:1'],
                'sms_limit_count'         => ['sometimes', 'nullable', 'integer', 'min:1'],
                'order_limit_minutes'     => ['sometimes', 'nullable', 'integer', 'min:1'],
                'order_limit_count'       => ['sometimes', 'nullable', 'integer', 'min:1'],
                'honeypot_filter_enabled' => ['sometimes', 'nullable', 'boolean'],
                'block_checkout_only'     => ['sometimes', 'nullable', 'boolean'],
                'status'                  => ['sometimes', 'integer'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'frequency_minute.min' => 'Frequency minute must be at least 1.',
                'order_limit.min'      => 'Order limit must be at least 1.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Update validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
