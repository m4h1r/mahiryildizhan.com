@php($isEdit = isset($person))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Name') }}</span>
        <input name="name" class="form-input-admin" value="{{ old('name', $person->name ?? '') }}" required>
        @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Surname') }}</span>
        <input name="surname" class="form-input-admin" value="{{ old('surname', $person->surname ?? '') }}" required>
        @error('surname')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Second Surname') }}</span>
        <input name="second_surname" class="form-input-admin" value="{{ old('second_surname', $person->second_surname ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Gender') }}</span>
        <select name="gender_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($genders as $gender)
                <option value="{{ $gender->id }}" @selected((string) old('gender_id', $person->gender_id ?? '') === (string) $gender->id)>{{ $gender->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Birthday') }}</span>
        <input type="date" name="birthday" class="form-input-admin" value="{{ old('birthday', optional($person->birthday ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Deathday') }}</span>
        <input type="date" name="deathday" class="form-input-admin" value="{{ old('deathday', optional($person->deathday ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Birth Place') }}</span>
        <input name="birth_place" class="form-input-admin" value="{{ old('birth_place', $person->birth_place ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Death Place') }}</span>
        <input name="death_place" class="form-input-admin" value="{{ old('death_place', $person->death_place ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Father') }}</span>
        <select name="father_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('father_id', $person->father_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }} {{ optional($candidate->birthday)->format('Y') ? '('.optional($candidate->birthday)->format('Y').')' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Mother') }}</span>
        <select name="mother_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('mother_id', $person->mother_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }} {{ optional($candidate->birthday)->format('Y') ? '('.optional($candidate->birthday)->format('Y').')' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Partner') }}</span>
        <select name="partner_id" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('partner_id', $person->partner_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Eye Color') }}</span>
        <select name="eye_color_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($eyeColors as $eyeColor)
                <option value="{{ $eyeColor->id }}" @selected((string) old('eye_color_id', $person->eye_color_id ?? '') === (string) $eyeColor->id)>{{ $eyeColor->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Blood Type') }}</span>
        <select name="blood_type_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($bloodTypes as $bloodType)
                <option value="{{ $bloodType->id }}" @selected((string) old('blood_type_id', $person->blood_type_id ?? '') === (string) $bloodType->id)>{{ $bloodType->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Hair Color') }}</span>
        <select name="hair_color_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($hairColors as $hairColor)
                <option value="{{ $hairColor->id }}" @selected((string) old('hair_color_id', $person->hair_color_id ?? '') === (string) $hairColor->id)>{{ $hairColor->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Phone') }}</span>
        <input name="mobile" class="form-input-admin" value="{{ old('mobile', $person->mobile ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Email') }}</span>
        <input name="email" type="email" class="form-input-admin" value="{{ old('email', $person->email ?? '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Picture Path') }}</span>
        <input name="picture" class="form-input-admin" value="{{ old('picture', $person->picture ?? '') }}" placeholder="{{ __('images/people/john.jpg') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Notes') }}</span>
        <textarea name="notes" class="form-input-admin min-h-28">{{ old('notes', $person->notes ?? '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Person') : __('Create Person') }}
    </button>

    <a href="{{ route('admin.people.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>
