<?php

namespace App\Http\Requests;

use App\Rules\UniqueProductIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequisitionRequest extends BaseCompanyRequest
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

                'request_date' => ['required', 'date'],
                'need_date' => ['required', 'date', 'after_or_equal:request_date'],
                'items' => ['required', 'array', 'min:1', new UniqueProductIds],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.unit' => ['required', 'string', 'max:50'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['nullable', 'numeric', 'min:0'],

                'note' => ['nullable', 'string'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'request_date.required' => 'Request date is required',
                'need_date.required' => 'Need by date is required',
                'need_date.after_or_equal' => 'Need by date must be equal or after request date',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.product_id.exists' =>
                'Selected product does not belong to the selected company.',
                'items.*.unit.required' => 'Unit is required',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',
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
