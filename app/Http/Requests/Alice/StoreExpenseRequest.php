<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
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
