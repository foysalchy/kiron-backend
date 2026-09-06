<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\BaseCompanyRequest;

class StoreReservationRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'table_ids' => ['required', 'array', 'min:1'],
                'table_ids.*' => ['integer', 'exists:tables,id'],
                'guest_name' => ['required', 'string', 'max:255'],
                'guest_phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
                'guest_count' => ['required', 'integer', 'min:1'],
                'reservation_date' => ['required', 'date'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
                'notes' => ['nullable', 'string'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'table_id.exists' => 'The selected table does not exist.',
                'end_time.after' => 'The end time must be after the start time.',
                'guest_phone.regex' => 'Phone number must be between 7 and 15 digits.',
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

