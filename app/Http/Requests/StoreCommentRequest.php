<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255'],
            'recaptcha_token' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.max' => 'Spam check failed.',
        ];
    }
}
