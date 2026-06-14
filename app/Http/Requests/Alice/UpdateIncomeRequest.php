<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'currency_id' => 'sometimes|exists:currencies,id',
            'income_source_id' => 'nullable|exists:income_sources,id',
            'income_type_id' => 'nullable|exists:income_types,id',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
