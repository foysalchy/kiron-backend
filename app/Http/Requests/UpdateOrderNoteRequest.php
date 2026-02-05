<?php
namespace App\Http\Requests;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateOrderNoteRequest extends UpdateBaseCompanyRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'note' => ['required', 'string', 'max:255'],
                'order_id' => ['required',],
                'type' => ['nullable', 'string',],
                'status' => ['nullable',],

            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'note.required' => 'Note  is required.',

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
