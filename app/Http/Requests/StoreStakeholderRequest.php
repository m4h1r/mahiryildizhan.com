<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStakeholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stakeholderId = $this->route('stakeholder')?->id;

        return [
            'vkn_tckn' => ['required', 'string', 'max:64', Rule::unique('stakeholders', 'vkn_tckn')->ignore($stakeholderId)],
            'title' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'tax_office_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'company_type' => ['required', Rule::in(['Company', 'Individual'])],
            'sector' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Passive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $country = $this->input('country');

        $this->merge([
            'country' => $country ? strtoupper((string) $country) : 'TR',
            'company_type' => $this->input('company_type', 'Company'),
            'status' => $this->input('status', 'Active'),
        ]);
    }
}
