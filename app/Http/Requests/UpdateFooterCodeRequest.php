<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFooterCodeRequest extends BaseCompanyRequest
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
        return array_merge($this->companyRules(), [
            'code' => [
                'sometimes',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $forbiddenTags = ['<?php', '<?=', '<?', '?>', '@php', '{{', '}}'];

                    foreach ($forbiddenTags as $tag) {
                        if (stripos($value, $tag) !== false) {
                            return $fail("The :attribute field cannot contain PHP or Blade tags ($tag).");
                        }
                    }
                },
            ],
            'status' => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'code.required' => 'The code field is required',
            'status.in'     => 'Status must be either 0 (Inactive) or 1 (Active)',
        ]);
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
