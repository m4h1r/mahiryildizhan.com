<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'second_surname' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'deathday' => ['nullable', 'date', 'after_or_equal:birthday'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'father_id' => ['nullable', 'integer', 'exists:people,id', 'different:mother_id', 'different:partner_id'],
            'mother_id' => ['nullable', 'integer', 'exists:people,id', 'different:father_id', 'different:partner_id'],
            'partner_id' => ['nullable', 'integer', 'exists:people,id', 'different:father_id', 'different:mother_id'],
            'gender_id' => ['nullable', 'integer', 'exists:genders,id'],
            'eye_color_id' => ['nullable', 'integer', 'exists:eye_colors,id'],
            'blood_type_id' => ['nullable', 'integer', 'exists:blood_types,id'],
            'hair_color_id' => ['nullable', 'integer', 'exists:hair_colors,id'],
            'picture' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
