<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTimelineEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'nullable|in:milestone,process',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'is_public' => 'nullable|boolean',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'order' => 'nullable|integer',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
