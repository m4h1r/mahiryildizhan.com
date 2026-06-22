@php($isEdit = isset($adage))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Owner') }}</span>
        <input name="owner" class="form-input-admin" value="{{ old('owner', $adage->owner ?? '') }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Language') }}</span>
        <select name="language_id" class="form-input-admin">
            <option value="">— {{ __('Select language') }} —</option>
            @foreach ($languages as $lang)
                <option value="{{ $lang->id }}" @selected((string) old('language_id', $adage->language_id ?? '') === (string) $lang->id)>
                    {{ $lang->name }} ({{ $lang->code }})
                </option>
            @endforeach
        </select>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Adage') }}</span>
        <textarea name="adage" class="form-input-admin min-h-28" required>{{ old('adage', $adage->adage ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Keywords') }}</span>
        <input name="keywords" class="form-input-admin" value="{{ old('keywords', $adage->keywords ?? '') }}" placeholder="comma,separated,keywords">
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Adage') : __('Create Adage') }}
    </button>

    <a href="{{ route('admin.adages.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">{{ __('Cancel') }}</a>
</div>