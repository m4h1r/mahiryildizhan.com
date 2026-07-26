<?php

namespace App\Http\Requests;

use App\Models\Food;
use Illuminate\Foundation\Http\FormRequest;

class StoreFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'calories_per_100g' => ['required', 'integer', 'min:0'],
            'carbs_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'sugar_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'protein_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'fat_per_100g' => ['required', 'numeric', 'min:0', 'max:100'],
            'unit_type' => ['required', 'in:gram,piece'],
            'grams_per_unit' => ['required_if:unit_type,piece', 'nullable', 'numeric', 'min:0.01'],
            'vitamins' => ['nullable', 'array'],
            'vitamins.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $vitamins = collect($this->input('vitamins', []))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->only(array_keys(Food::VITAMINS))
            ->all();

        $this->merge([
            'vitamins' => $vitamins,
            'grams_per_unit' => $this->input('unit_type') === 'piece' ? $this->input('grams_per_unit') : null,
        ]);
    }
}
