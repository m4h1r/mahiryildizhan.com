@props([
    'action' => route('search'),
    'name' => 'q',
    'value' => '',
    'placeholder' => __('Search...'),
    'buttonText' => __('Search'),
    'wrapperClass' => 'flex w-full items-center gap-2',
    'inputClass' => 'w-full rounded-full border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-gray-200 dark:focus:ring-gray-800',
    'buttonClass' => 'rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100',
])

@php($value = $value !== '' ? $value : request()->query($name, ''))

<form method="GET" action="{{ $action }}" class="{{ $wrapperClass }}">
    <input
        type="search"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        aria-label="{{ $placeholder }}"
        class="{{ $inputClass }}"
    >
    <button type="submit" class="{{ $buttonClass }}">
        {{ $buttonText }}
    </button>
</form>
