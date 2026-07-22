@php
    $brandPrimary = \App\Models\Setting::get('brand_primary');
    $brandSecondary = \App\Models\Setting::get('brand_secondary');
@endphp
@if ($brandPrimary || $brandSecondary)
<style nonce="{{ request()->attributes->get('csp_nonce', '') }}">
    :root {
        @if ($brandPrimary)
        --brand-primary: {{ $brandPrimary }};
        @endif
        @if ($brandSecondary)
        --brand-secondary: {{ $brandSecondary }};
        @endif
    }
</style>
@endif
