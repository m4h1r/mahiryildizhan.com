@extends('admin.layout', ['title' => 'Settings', 'heading' => 'Settings'])

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        @php
            $groupLabels = [
                'general' => __('General'),
                'analytics' => __('Analytics'),
                'advertising' => __('Advertising'),
                'seo' => __('SEO'),
                'mailchimp' => __('Mailchimp'),
                'recaptcha' => __('reCAPTCHA'),
                'weather' => __('Weather'),
                'social_links' => __('Social Links'),
            ];
        @endphp

        @foreach ($settingsByGroup as $group => $items)
            <section class="card-admin p-6">
                <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ $groupLabels[$group] ?? ucfirst($group) }}</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($items as $item)
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            <span class="mb-1 block">{{ __($item['label']) }}</span>
                            @if ($item['key'] === 'admin_locale')
                                <select name="settings[{{ $item['key'] }}]" class="form-input-admin">
                                    @php($selectedLocale = old('settings.'.$item['key'], $item['value'] ?: 'tr'))
                                    <option value="tr" @selected($selectedLocale === 'tr')>{{ __('Turkish') }}</option>
                                    <option value="en" @selected($selectedLocale === 'en')>{{ __('English') }}</option>
                                </select>
                            @else
                                <input
                                    name="settings[{{ $item['key'] }}]"
                                    type="{{ $item['is_secret'] ? 'password' : 'text' }}"
                                    class="form-input-admin"
                                    value="{{ old('settings.'.$item['key'], $item['value']) }}"
                                    autocomplete="off"
                                >
                            @endif
                            <span class="mt-1 block text-xs text-gray-500">{{ $item['key'] }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">{{ __('Save Settings') }}</button>
        </div>
    </form>
@endsection
