@php($isEdit = isset($interaction))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Person</span>
        <select name="person_id" class="form-input-admin" required>
            <option value="">Select person</option>
            @foreach ($people as $person)
                <option value="{{ $person->id }}" @selected((string) old('person_id', $interaction->person_id ?? '') === (string) $person->id)>
                    {{ $person->surname }}, {{ $person->name }}
                </option>
            @endforeach
        </select>
        @error('person_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Type</span>
        <select name="interaction_type_id" class="form-input-admin">
            <option value="">None</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected((string) old('interaction_type_id', $interaction->interaction_type_id ?? '') === (string) $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Date</span>
        <input type="date" name="date" class="form-input-admin" value="{{ old('date', isset($interaction) ? optional($interaction->date)->toDateString() : now()->toDateString()) }}" required>
        @error('date')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Effect</span>
        <input name="effect" class="form-input-admin" value="{{ old('effect', $interaction->effect ?? '') }}" placeholder="Positive / Neutral / Negative">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Notes</span>
        <textarea name="notes" class="form-input-admin min-h-32">{{ old('notes', $interaction->notes ?? '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? 'Update Interaction' : 'Create Interaction' }}
    </button>

    <a href="{{ route('admin.interactions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        Cancel
    </a>
</div>
