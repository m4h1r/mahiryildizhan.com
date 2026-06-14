<?php

namespace App\Http\Requests\Alice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'second_surname' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'deathday' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_place' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'address' => 'nullable|string',
            'gender_id' => 'nullable|exists:genders,id',
            'eye_color_id' => 'nullable|exists:eye_colors,id',
            'blood_type_id' => 'nullable|exists:blood_types,id',
            'hair_color_id' => 'nullable|exists:hair_colors,id',
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
            'partner_id' => 'nullable|exists:people,id',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => $validator->errors()->toArray()],
        ], 422));
    }
}
