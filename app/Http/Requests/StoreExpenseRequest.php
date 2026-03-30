<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'stakeholder_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'expense_type_id' => ['nullable', 'integer', 'exists:expense_types,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'company_expense' => ['nullable', 'boolean'],
            'paid_by_others' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_expense' => $this->boolean('company_expense'),
            'paid_by_others' => $this->boolean('paid_by_others'),
            'tax' => $this->input('tax', 0),
            'quantity' => $this->input('quantity', 1),
        ]);
    }
}
