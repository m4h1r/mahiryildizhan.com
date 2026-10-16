@props([
    'type' => 'info', // success | warning | danger | info — never a 5th role (V28)
    'dismissible' => false,
])

@php
    $role = in_array($type, ['success', 'warning', 'danger', 'info'], true) ? $type : 'info';

    // 3-token recipe + icon + word (V28): colour is never the only signal.
    $word = [
        'success' => __('Success'),
        'warning' => __('Warning'),
        'danger' => __('Error'),
        'info' => __('Info'),
    ][$role];

    $icon = [
        'success' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'danger' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        'info' => 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
    ][$role];
@endphp

<div
    role="{{ $role === 'danger' ? 'alert' : 'status' }}"
    aria-live="{{ $role === 'danger' ? 'assertive' : 'polite' }}"
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3 text-sm']) }}
    style="background-color: var(--color-{{ $role }}-bg); color: var(--color-{{ $role }}-text); border-color: var(--color-{{ $role }}-border);"
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
    <svg class="mt-0.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
    </svg>
    <div class="flex-1">
        <span class="font-semibold">{{ $word }}.</span>
        <span>{{ $slot }}</span>
    </div>
    @if ($dismissible)
        <button type="button" @click="visible = false" class="-mr-1 ml-2 shrink-0 opacity-70 hover:opacity-100" aria-label="{{ __('Dismiss') }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
