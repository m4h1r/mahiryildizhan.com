{{-- Language switcher (T10) — text labels (TR/EN), never flag icons. Posts the
     chosen locale to the session-based switcher and returns to the current page.
     NOTE: this site uses session-based locale, not URL-based locales, so there
     is no /en/ prefix — see project-standards.md "Localization". --}}
@php
    $current = app()->getLocale();
    $locales = ['tr' => 'TR', 'en' => 'EN'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border border-gray-300 p-0.5 dark:border-gray-700']) }}
     role="group" aria-label="{{ __('Language') }}">
    @foreach ($locales as $code => $label)
        @php $active = $current === $code; @endphp
        <a href="{{ route('locale.switch', $code) }}"
           @if ($active) aria-current="true" @endif
           data-no-prefetch
           class="rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide transition
                  {{ $active
                      ? 'bg-[#1d1d1f] text-white dark:bg-gray-100 dark:text-gray-900'
                      : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' }}">
            {{ $label }}
            <span class="sr-only">{{ $code === 'tr' ? __('Turkish') : __('English') }}</span>
        </a>
    @endforeach
</div>
