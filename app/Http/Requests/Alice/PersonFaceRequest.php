<?php

namespace App\Http\Requests\Alice;

use App\Support\Crm\CrmRules;

/**
 * multipart/form-data with `image`, or JSON with `embedding` (and optionally
 * `image_base64`). At least one of image / image_base64 / embedding is required.
 */
class PersonFaceRequest extends AliceRequest
{
    protected function prepareForValidation(): void
    {
        // multipart sends JSON fields as strings
        foreach (['embedding', 'bbox', 'landmarks'] as $field) {
            $value = $this->input($field);
            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    public function rules(): array
    {
        $rules = CrmRules::face(false);
        $rules['image_base64'] = ['nullable', 'string', 'max:15000000'];

        return $rules;
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (! $this->hasFile('image') && ! $this->filled('image_base64') && ! $this->filled('embedding')) {
                $validator->errors()->add('image', 'image, image_base64 veya embedding alanlarından en az biri gerekli.');
            }
        }];
    }

    public function imageBinary(): ?string
    {
        $raw = (string) $this->input('image_base64', '');
        if ($raw === '') {
            return null;
        }
        $raw = preg_replace('#^data:image/[a-z0-9.+-]+;base64,#i', '', $raw);
        $binary = base64_decode((string) $raw, true);

        return $binary === false ? null : $binary;
    }
}
