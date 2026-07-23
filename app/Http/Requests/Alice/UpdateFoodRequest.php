<?php

namespace App\Http\Requests\Alice;

use App\Models\Food;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'calories_per_100g' => 'sometimes|integer|min:0',
            'carbs_per_100g' => 'sometimes|numeric|min:0|max:100',
            'sugar_per_100g' => 'sometimes|numeric|min:0|max:100',
            'fat_per_100g' => 'sometimes|numeric|min:0|max:100',
            'unit_type' => 'sometimes|in:gram,piece',
            'grams_per_unit' => 'nullable|numeric|min:0.01',
            'vitamins' => 'nullable|array',
            'vitamins.*' => 'nullable|numeric|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('vitamins')) {
            $vitamins = collect($this->input('vitamins', []))
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->only(array_keys(Food::VITAMINS))
                ->all();

            $this->merge(['vitamins' => $vitamins]);
        }
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
