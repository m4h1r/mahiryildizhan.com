<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreConsumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_id' => 'required_without:food|integer|exists:foods,id',
            'food' => 'required_without:food_id|string',
            'consumed_on' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
