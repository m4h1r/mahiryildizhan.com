@props([
    'name',
    'label',
    'type' => 'text',
    'id' => null,
    'value' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div class="relative">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        placeholder=" "
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-input-admin peer']) }}
    />
    <label
        for="{{ $id }}"
        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-gray-400 transition-all duration-150 peer-focus:top-0 peer-focus:-translate-y-full peer-focus:text-xs peer-focus:text-[var(--color-brand)] peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:-translate-y-full peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-gray-500 dark:peer-[:not(:placeholder-shown)]:text-gray-400"
    >
        {{ $label }}
    </label>
</div>
