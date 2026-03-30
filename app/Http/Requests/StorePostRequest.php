<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $postId = $this->route('post')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($postId)],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'cover' => ['nullable', 'string', 'max:255'],
            'cover_upload' => ['nullable', 'image', 'max:6144'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'language_id' => ['nullable', 'integer', 'exists:post_languages,id'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'schema_type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'publish_date' => ['nullable', 'date'],
            'published' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'published' => $this->boolean('published'),
            'status' => $this->input('status', 'draft'),
            'schema_type' => $this->input('schema_type', 'Article'),
        ]);
    }
}
