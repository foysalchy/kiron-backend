<?php

namespace App\Http\Requests\Saas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterBrandRequest extends FormRequest
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
            'logo' => 'sometimes|nullable|image|max:2048',
            'link' => 'sometimes|nullable|url',
        ];
    }
}
