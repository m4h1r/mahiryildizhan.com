@props(['type' => 'info', 'dismissible' => false])

@php
    $type = in_array($type, ['success', 'warning', 'danger', 'info'], true) ? $type : 'info';

    $icons = [
        'success' => 'M5 13l4 4L19 7',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'danger' => 'M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    ];

    $labels = [
        'success' => __('Success'),
        'warning' => __('Warning'),
        'danger' => __('Error'),
        'info' => __('Info'),
    ];
@endphp

<div
    {{ $attributes->merge(['class' => "status-message status-message-{$type}"]) }}
    role="status"
    aria-live="polite"
    @if ($dismissible)
        x-data="{ visible: true }"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    @endif
>
    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$type] }}" />
    </svg>
    <span class="sr-only">{{ $labels[$type] }}:</span>
    <div class="flex-1">{{ $slot }}</div>
    @if ($dismissible)
        <button
            type="button"
            @click="visible = false"
            class="shrink-0 opacity-60 transition-opacity hover:opacity-100"
            aria-label="{{ __('Dismiss') }}"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
