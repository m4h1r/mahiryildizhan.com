@php($isEdit = isset($income))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Date') }}</span>
        <input type="date" name="date" class="form-input-admin" value="{{ old('date', isset($income) ? optional($income->date)->toDateString() : now()->toDateString()) }}" required>
        @error('date')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Amount') }}</span>
        <input type="number" step="0.01" min="0" name="amount" class="form-input-admin" value="{{ old('amount', $income->amount ?? 0) }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Saat</span>
        <input type="number" step="0.01" min="0" name="hours" class="form-input-admin" value="{{ old('hours', $income->hours ?? '') }}">
        @error('hours')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    @php($initialSourceableKey = old('sourceable', $currentSourceableKey ?? ''))
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Income Source') }}</span>
        <select name="sourceable" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            <optgroup label="{{ __('People') }}">
                @foreach ($people as $person)
                    <option value="person:{{ $person->id }}" @selected($initialSourceableKey === 'person:'.$person->id)>{{ $person->fullName() }}</option>
                @endforeach
            </optgroup>
            <optgroup label="{{ __('Stakeholders') }}">
                @foreach ($stakeholders as $stakeholder)
                    <option value="stakeholder:{{ $stakeholder->id }}" @selected($initialSourceableKey === 'stakeholder:'.$stakeholder->id)>{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</option>
                @endforeach
            </optgroup>
        </select>
        @error('sourceable')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Income Type') }}</span>
        <select name="income_type_id" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected((string) old('income_type_id', $income->income_type_id ?? ($isEdit ? '' : '24')) === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Currency') }}</span>
        <select name="currency_id" class="form-input-admin" required>
            @foreach ($currencies as $currency)
                <option value="{{ $currency->id }}" @selected((string) old('currency_id', $income->currency_id ?? ($isEdit ? '' : '1')) === (string) $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
            @endforeach
        </select>
    </label>

    <input type="hidden" name="user_id" value="{{ old('user_id', $income->user_id ?? auth()->id()) }}">

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Description') }}</span>
        <textarea name="description" class="form-input-admin min-h-28">{{ old('description', $income->description ?? '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Income') : __('Create Income') }}
    </button>

    <a href="{{ route('admin.incomes.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>
