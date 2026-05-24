<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePeriodTypeRequest extends UpdateBaseCompanyRequest
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
    $companyId = $this->input('company_id') ?? $this->user()->company_id;
    
    return array_merge(
        $this->companyRules(),
        [
            'type' => [
                'sometimes', 
                'string', 
                'max:255', 
                Rule::unique('period_types', 'type')
                    ->where('company_id', $companyId)
                    ->ignore($this->route('period_type')),
            ],
            'status' => ['sometimes', 'integer'],
        ]
    );
}
    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'type.unique' => 'This period type name is already in use.',
                'status.in'   => 'Status must be either 1 (Active) or 0 (Inactive).',
            ]
        );
    }

    /**
     * Return JSON response on validation failure
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
