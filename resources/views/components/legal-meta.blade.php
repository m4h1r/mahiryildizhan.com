{{-- Effective date + version for legal pages (E12). Source of truth: config/legal.php --}}
<p class="text-sm text-gray-500">
    {{ __('Last updated') }}:
    {{ \Illuminate\Support\Carbon::parse(config('legal.effective_date'))->translatedFormat('d F Y') }}
    &middot; v{{ config('legal.version') }}
</p>
