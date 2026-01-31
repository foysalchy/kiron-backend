<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCourierMethodRequest extends UpdateBaseCompanyRequest
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
        $methodId = $this->route('id'); 

        return array_merge($this->companyRules(), [
            'name'    => ['sometimes', 'required', 'string', 'max:255'],
            'slug'    => ['sometimes', 'required', 'string', 'max:255', 
                Rule::unique('courier_methods', 'slug')->ignore($methodId)
            ],
            'details' => ['nullable', 'array'],
            'status'  => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Courier method name is required',
                'slug.required' => 'Courier method slug is required',
                'slug.unique'   => 'This slug has already been taken',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
