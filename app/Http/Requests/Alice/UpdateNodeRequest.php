<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateNodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('nodes', 'name')->ignore($id)],
            'text_color' => 'nullable|string|max:50',
            'text_size' => 'nullable|integer',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
