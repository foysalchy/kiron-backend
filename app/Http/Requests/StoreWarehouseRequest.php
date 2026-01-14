<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\BaseCompanyRequest;

class StoreWarehouseRequest extends BaseCompanyRequest
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
            'name'       => ['required','string','max:255'],
            'location'   => ['nullable','string','max:500'],
            'status'     => ['required','integer'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),

            [
                'name.required'     => 'Warehouse Name is required.',
                'status.required'   => 'Warehouse status is required.',
                'location.max'      => 'Location cannot exceed 500 characters.',
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
