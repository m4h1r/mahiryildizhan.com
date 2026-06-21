<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'income_source_id' => ['nullable', 'integer', 'exists:income_sources,id'],
            'income_type_id' => ['nullable', 'integer', 'exists:income_types,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'hours' => ['nullable', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
