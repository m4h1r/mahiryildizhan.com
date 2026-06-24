<?php

namespace App\Http\Requests;

use App\Models\Person;
use App\Models\Stakeholder;
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
            'sourceable' => ['nullable', 'string', function ($attribute, $value, $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                [$type, $id] = array_pad(explode(':', $value, 2), 2, null);

                $model = match ($type) {
                    'person' => Person::class,
                    'stakeholder' => Stakeholder::class,
                    default => null,
                };

                if ($model === null || ! is_numeric($id) || ! $model::whereKey($id)->exists()) {
                    $fail('Seçilen gelir kaynağı geçersiz.');
                }
            }],
        ];
    }
}
