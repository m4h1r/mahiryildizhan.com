<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title' => 'sometimes|string|max:500',
            'slug' => ['nullable', 'string', 'max:500', Rule::unique('posts', 'slug')->ignore($id)],
            'body' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,published,archived',
            'published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'publish_date' => 'nullable|date',
            'category_id' => 'nullable|exists:post_categories,id',
            'language_id' => 'nullable|exists:post_languages,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
