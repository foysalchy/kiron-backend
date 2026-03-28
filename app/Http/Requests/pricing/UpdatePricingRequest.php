<?php

namespace App\Http\Requests\pricing;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePricingRequest extends FormRequest
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
        return [
            'name' => 'sometimes|required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'monthly_regular_price' => 'sometimes|required|numeric|min:0',
            'monthly_discount_price' => 'nullable|numeric|min:0|lte:monthly_regular_price',
            'yearly_regular_price' => 'sometimes|required|numeric|min:0',
            'yearly_discount_price' => 'nullable|numeric|min:0|lte:yearly_regular_price',
            'order_limitation' => 'sometimes|required|integer|min:0',
            'user_limitation' => 'sometimes|required|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'required|string|max:255',
            'is_featured' => 'boolean',
            'status' => ['sometimes', 'required', 'integer'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'monthly_discount_price.lte' => 'The monthly discount price must be less than or equal to the regular monthly price.',
            'yearly_discount_price.lte' => 'The yearly discount price must be less than or equal to the regular yearly price.',
            'features.*.required' => 'The feature list fields cannot be empty.',
            'features.*.string' => 'Each feature must be a valid text string.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors occurred.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
