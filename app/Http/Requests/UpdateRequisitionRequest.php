<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRequisitionRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $user = $this->user();
        $companyId = $user->isSuperAdmin()
            ? $this->input('company_id')
            : $user->company_id;
        return array_merge(
            $this->companyRules(),
            [
                'request_date' => ['sometimes', 'date'],
                'need_date' => ['sometimes', 'date', 'after_or_equal:request_date'],
                'items' => ['sometimes', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.unit' => ['required_with:items', 'string', 'max:50'],
                'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
                'items.*.price' => ['nullable', 'numeric', 'min:0'],
                'note' => ['nullable', 'string'],
                'status' => ['sometimes', 'integer', 'in:0,1,2,3'],
                'reject_reason' => ['nullable', 'string', 'required_if:status,2'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'request_date.date' => 'Request date must be a valid date',
                'need_date.date' => 'Need by date must be a valid date',
                'need_date.after_or_equal' => 'Need by date must be equal or after request date',
                'items.min' => 'At least one item is required',
                     'items.*.product_id.required' => 'Product is required for each item',
                'items.*.product_id.exists' =>
                'Selected product does not belong to the selected company.',
                'items.*.unit.required_with' => 'Unit is required',
                'items.*.quantity.required_with' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',
                'status.in' => 'Invalid status value',
                'reject_reason.required_if' => 'Reject reason is required when status is rejected',
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
