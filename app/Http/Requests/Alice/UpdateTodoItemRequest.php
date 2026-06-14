<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTodoItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'cost_try' => 'nullable|numeric|min:0',
            'time_cost_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'is_bucketlist' => 'nullable|boolean',
            'is_completed' => 'nullable|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
