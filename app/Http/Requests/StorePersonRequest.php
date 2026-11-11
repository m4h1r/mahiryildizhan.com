<?php

namespace App\Http\Requests;

use App\Support\Crm\CrmRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Chip inputs post arrays; a cleared list posts nothing — treat as empty.
        $this->merge(collect(['interests', 'hobbies', 'tendencies', 'relationship_bases', 'channels'])
            ->mapWithKeys(fn ($field) => [$field => $this->input($field, [])])
            ->all());
    }

    public function rules(): array
    {
        $person = $this->route('person');

        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'second_surname' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'deathday' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'father_id' => ['nullable', 'integer', 'exists:people,id', 'different:mother_id', 'different:partner_id'],
            'mother_id' => ['nullable', 'integer', 'exists:people,id', 'different:father_id', 'different:partner_id'],
            'partner_id' => ['nullable', 'integer', 'exists:people,id', 'different:father_id', 'different:mother_id'],
            'gender_id' => ['nullable', 'integer', 'exists:genders,id'],
            'eye_color_id' => ['nullable', 'integer', 'exists:eye_colors,id'],
            'blood_type_id' => ['nullable', 'integer', 'exists:blood_types,id'],
            'hair_color_id' => ['nullable', 'integer', 'exists:hair_colors,id'],
            'picture' => ['nullable', 'string', 'max:2048'],
            'picture_file' => ['nullable', 'image', 'max:4096'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],

            ...CrmRules::person($person?->id),

            'channels' => ['array', 'max:50'],
            'channels.*.id' => ['nullable', 'integer'],
            ...CrmRules::channel('channels.*.'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $d = $this->integer('birth_day') ?: null;
                $m = $this->integer('birth_month') ?: null;
                $y = $this->integer('birth_year') ?: null;

                if ($d && $m && ! checkdate($m, $d, $y ?: 2000)) {
                    $validator->errors()->add('birth_day', __('This day does not exist in the selected month.'));

                    return;
                }

                $death = $this->input('deathday');
                if ($death && $d && $m && $y && checkdate($m, $d, $y)
                    && Carbon::parse($death)->lt(Carbon::createFromDate($y, $m, $d)->startOfDay())) {
                    $validator->errors()->add('deathday', __('The date of death cannot be before the date of birth.'));
                }
            },
        ];
    }
}
