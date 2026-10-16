@props([
    'href' => null,
    'type' => 'submit',
])

{{-- Primary CTA (V31) — one per template, colour is --color-primary (never a
     status colour / V28). Copy comes from lang ui.cta.* in both locales. Fires
     a consent-gated cta_primary_click event via data-cta. --}}
@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2';
    $style = 'background-color: var(--color-primary);';
@endphp

@if ($href)
    <a href="{{ $href }}" data-cta="primary"
       {{ $attributes->merge(['class' => $classes]) }} style="{{ $style }}">
        {{ $slot->isEmpty() ? __('ui.cta.primary') : $slot }}
    </a>
@else
    <button type="{{ $type }}" data-cta="primary"
            {{ $attributes->merge(['class' => $classes]) }} style="{{ $style }}">
        {{ $slot->isEmpty() ? __('ui.cta.primary') : $slot }}
    </button>
@endif
