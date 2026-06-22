@php($isEdit = isset($stakeholder))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">VKN / TCKN</span>
        <input name="vkn_tckn" class="form-input-admin" value="{{ old('vkn_tckn', $stakeholder->vkn_tckn ?? '') }}" required>
        @error('vkn_tckn')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Title / Company') }}</span>
        <input name="title" class="form-input-admin" value="{{ old('title', $stakeholder->title ?? '') }}">
        @error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Name') }}</span>
        <input name="name" class="form-input-admin" value="{{ old('name', $stakeholder->name ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Surname') }}</span>
        <input name="surname" class="form-input-admin" value="{{ old('surname', $stakeholder->surname ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Tax Office') }}</span>
        <input name="tax_office_name" class="form-input-admin" value="{{ old('tax_office_name', $stakeholder->tax_office_name ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Sector') }}</span>
        <input name="sector" class="form-input-admin" value="{{ old('sector', $stakeholder->sector ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Email') }}</span>
        <input name="email" class="form-input-admin" type="email" value="{{ old('email', $stakeholder->email ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Phone') }}</span>
        <input name="phone" class="form-input-admin" value="{{ old('phone', $stakeholder->phone ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Website') }}</span>
        <input name="website" class="form-input-admin" type="url" value="{{ old('website', $stakeholder->website ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('City') }}</span>
        <input name="city" class="form-input-admin" value="{{ old('city', $stakeholder->city ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Country (ISO-2)') }}</span>
        <input name="country" class="form-input-admin" maxlength="2" value="{{ old('country', $stakeholder->country ?? 'TR') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Company Type') }}</span>
        <select name="company_type" class="form-input-admin">
            @foreach (['Company', 'Individual'] as $type)
                <option value="{{ $type }}" @selected(old('company_type', $stakeholder->company_type ?? 'Company') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Status') }}</span>
        <select name="status" class="form-input-admin">
            @foreach (['Active', 'Passive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $stakeholder->status ?? 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Address') }}</span>
        <textarea name="address" class="form-input-admin min-h-24">{{ old('address', $stakeholder->address ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Note') }}</span>
        <textarea name="note" class="form-input-admin min-h-28">{{ old('note', $stakeholder->note ?? '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Stakeholder') : __('Create Stakeholder') }}
    </button>

    <a href="{{ route('admin.stakeholders.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>
