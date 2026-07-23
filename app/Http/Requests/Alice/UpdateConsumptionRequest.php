<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateConsumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_id' => 'sometimes|integer|exists:foods,id',
            'food' => 'sometimes|string',
            'consumed_on' => 'sometimes|date',
            'quantity' => 'sometimes|numeric|min:0.01',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
