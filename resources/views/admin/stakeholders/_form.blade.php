@php($isEdit = isset($stakeholder))
@php($taxOffices = $taxOffices ?? \App\Models\TaxOffice::query()->orderBy('name')->get())
@php($sectors = $sectors ?? \App\Models\Sector::query()->orderBy('name')->get())
@php($selectedTaxOffice = (string) old('tax_office_id', $stakeholder->tax_office_id ?? ''))
@php($selectedSector = (string) old('sector_id', $stakeholder->sector_id ?? ''))
@php($selectedCompanyType = old('company_type', $stakeholder->company_type ?? 'Company'))
@php($selectedStatus = old('status', $stakeholder->status ?? 'Active'))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Tax Office') }}</span>
        <select name="tax_office_id" class="form-input-admin">
            <option value="">{{ __('Select') }}</option>
            @foreach ($taxOffices as $taxOffice)
                <option value="{{ $taxOffice->id }}" @selected($selectedTaxOffice === (string) $taxOffice->id)>{{ $taxOffice->name }}</option>
            @endforeach
        </select>
        @error('tax_office_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">VKN / TCKN</span>
        <input name="vkn_tckn" class="form-input-admin" value="{{ old('vkn_tckn', $stakeholder->vkn_tckn ?? '') }}" required>
        @error('vkn_tckn')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Sector') }}</span>
        <select name="sector_id" class="form-input-admin">
            <option value="">{{ __('Select') }}</option>
            @foreach ($sectors as $sector)
                <option value="{{ $sector->id }}" @selected($selectedSector === (string) $sector->id)>{{ $sector->name }}</option>
            @endforeach
        </select>
        @error('sector_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
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

    <div class="md:col-span-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:gap-6">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200 sm:w-40">
            <span class="mb-1 block">{{ __('Country (ISO-2)') }}</span>
            <input name="country" class="form-input-admin" maxlength="2" value="{{ old('country', $stakeholder->country ?? 'TR') }}">
        </label>

        <div class="flex items-center gap-3 sm:pb-2.5">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Company Type') }}</span>
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="hidden" name="company_type" value="Individual">
                <input type="checkbox" name="company_type" value="Company" class="peer sr-only" @checked($selectedCompanyType === 'Company')>
                <span class="relative h-6 w-11 rounded-full bg-gray-300 transition-colors peer-checked:bg-[var(--color-brand)] after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5 dark:bg-gray-600"></span>
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('Company') }}</span>
            </label>
        </div>

        <label class="inline-flex cursor-pointer items-center gap-2 sm:pb-2.5">
            <input type="hidden" name="status" value="Passive">
            <input type="checkbox" name="status" value="Active" class="h-5 w-5 rounded border-gray-300 text-[var(--color-brand)] focus:ring-[var(--color-brand)]" @checked($selectedStatus === 'Active')>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Active') }}</span>
        </label>
    </div>

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
