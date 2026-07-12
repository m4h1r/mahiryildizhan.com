<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'sometimes|date',
            'description' => 'nullable|string|max:1000',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'currency_id' => 'sometimes|exists:currencies,id',
            'expense_type_id' => 'nullable|exists:expense_types,id',
            'stakeholder_id' => 'nullable|exists:stakeholders,id',
            'stakeholder' => 'nullable|string',
            'company_expense' => 'nullable|boolean',
            'paid_by_others' => 'nullable|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'validation_failed',
                'message' => 'Doğrulama hatası',
                'fields' => $validator->errors()->toArray(),
            ],
        ], 422));
    }
}
