@extends('admin.layout', ['title' => 'Settings', 'heading' => 'Settings'])

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        @php
            $groupLabels = [
                'brand'        => __('Marka'),
                'time_ranges'  => __('Saat Kadranı'),
                'financial'    => __('Finansal'),
                'general'      => __('General'),
                'analytics'    => __('Analytics'),
                'advertising'  => __('Advertising'),
                'seo'          => __('SEO'),
                'mailchimp'    => __('Mailchimp'),
                'recaptcha'    => __('reCAPTCHA'),
                'weather'      => __('Weather'),
                'social_links' => __('Social Links'),
                'about'        => __('About Page'),
            ];
        @endphp

        <div x-data="{ activeTab: '{{ request('tab', old('_tab', 'brand')) }}' }">

            {{-- Tab başlıkları --}}
            <div class="mb-6 flex flex-wrap gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($settingsByGroup as $group => $items)
                    <button
                        type="button"
                        @click="activeTab = '{{ $group }}'"
                        :class="activeTab === '{{ $group }}'
                            ? 'bg-white shadow text-gray-900 dark:bg-gray-700 dark:text-gray-100'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    >
                        {{ $groupLabels[$group] ?? ucfirst($group) }}
                    </button>
                @endforeach
            </div>

            {{-- Tab içerikleri --}}
            @foreach ($settingsByGroup as $group => $items)
                <section
                    x-show="activeTab === '{{ $group }}'"
                    x-cloak
                    class="card-admin p-6"
                >
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ $groupLabels[$group] ?? ucfirst($group) }}</h2>

                    @if ($group === 'about')
                        @php
                            $aboutEn = collect($items)->firstWhere('key', 'about_content_en');
                            $aboutTr = collect($items)->firstWhere('key', 'about_content_tr');
                        @endphp
                        <div x-data="{ tab: 'en' }" class="mt-4 space-y-4">
                            <div class="flex gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 w-fit dark:border-gray-700 dark:bg-gray-800">
                                <button type="button"
                                    @click="tab = 'en'"
                                    :class="tab === 'en' ? 'bg-white shadow dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    class="rounded-lg px-5 py-2 text-sm font-medium transition">
                                    🇬🇧 English
                                </button>
                                <button type="button"
                                    @click="tab = 'tr'"
                                    :class="tab === 'tr' ? 'bg-white shadow dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    class="rounded-lg px-5 py-2 text-sm font-medium transition">
                                    🇹🇷 Türkçe
                                </button>
                            </div>

                            <div x-show="tab === 'en'">
                                <p class="mb-2 text-xs text-gray-400">about_content_en</p>
                                <div
                                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('settings.about_content_en', $aboutEn['value'] ?? '')) }} })"
                                    x-init="init()"
                                    class="space-y-3"
                                >
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Bold') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Italic') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H2</button>
                                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H3</button>
                                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">• {{ __('List') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">1. {{ __('List') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Quote') }}</button>
                                        <button type="button" @click="editor?.chain().focus().undo().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↩ {{ __('Undo') }}</button>
                                        <button type="button" @click="editor?.chain().focus().redo().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↪ {{ __('Redo') }}</button>
                                    </div>
                                    <div x-ref="editor"
                                        class="prose prose-sm min-h-48 max-w-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    </div>
                                    <textarea name="settings[about_content_en]" x-model="content" class="hidden"></textarea>
                                </div>
                            </div>

                            <div x-show="tab === 'tr'" x-cloak>
                                <p class="mb-2 text-xs text-gray-400">about_content_tr</p>
                                <div
                                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('settings.about_content_tr', $aboutTr['value'] ?? '')) }} })"
                                    x-init="init()"
                                    class="space-y-3"
                                >
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Bold') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Italic') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H2</button>
                                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H3</button>
                                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">• {{ __('List') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">1. {{ __('List') }}</button>
                                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">{{ __('Quote') }}</button>
                                        <button type="button" @click="editor?.chain().focus().undo().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↩ {{ __('Undo') }}</button>
                                        <button type="button" @click="editor?.chain().focus().redo().run()"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↪ {{ __('Redo') }}</button>
                                    </div>
                                    <div x-ref="editor"
                                        class="prose prose-sm min-h-48 max-w-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    </div>
                                    <textarea name="settings[about_content_tr]" x-model="content" class="hidden"></textarea>
                                </div>
                            </div>
                        </div>
                    @elseif ($group === 'time_ranges')
                        <div x-data="{ day: {{ now()->dayOfWeek }} }" class="mt-4 space-y-4">
                            <div class="flex flex-wrap gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 w-fit dark:border-gray-700 dark:bg-gray-800">
                                @foreach ($dayLabels as $dayIndex => $dayLabel)
                                    <button type="button"
                                        @click="day = {{ $dayIndex }}"
                                        :class="day === {{ $dayIndex }} ? 'bg-white shadow dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                        class="rounded-lg px-4 py-2 text-sm font-medium transition">
                                        {{ $dayLabel }}
                                    </button>
                                @endforeach
                            </div>

                            @foreach ($dayLabels as $dayIndex => $dayLabel)
                                <div x-show="day === {{ $dayIndex }}" x-cloak>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.time-ranges.sync', $dayIndex) }}"
                                        x-data="timeRangeRepeater({{ Js::from(($timeRanges->get($dayIndex) ?? collect())->map(fn ($r) => [
                                            'starts_at' => substr((string) $r->starts_at, 0, 5),
                                            'ends_at' => substr((string) $r->ends_at, 0, 5),
                                            'label' => $r->label,
                                            'color' => $r->color,
                                        ])->values()) }})"
                                        class="space-y-3"
                                    >
                                        @csrf
                                        <template x-for="(row, idx) in rows" :key="idx">
                                            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                                <input type="time" x-model="row.starts_at" :name="`ranges[${idx}][starts_at]`" class="form-input-admin w-28" required>
                                                <span class="text-xs text-gray-400">–</span>
                                                <input type="time" x-model="row.ends_at" :name="`ranges[${idx}][ends_at]`" class="form-input-admin w-28" required>
                                                <input type="text" x-model="row.label" :name="`ranges[${idx}][label]`" placeholder="{{ __('Etiket (örn. Uyku)') }}" class="form-input-admin min-w-[8rem] flex-1" required>
                                                <input type="color" x-model="row.color" :name="`ranges[${idx}][color]`" class="h-11 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700">
                                                <button type="button" @click="rows.splice(idx, 1)" class="admin-btn-sm admin-btn-danger">{{ __('Sil') }}</button>
                                            </div>
                                        </template>

                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="rows.push({ starts_at: '00:00', ends_at: '00:00', label: '', color: '#3B82F6' })" class="admin-btn admin-btn-ghost">
                                                + {{ __('Aralık Ekle') }}
                                            </button>
                                            <button type="submit" class="admin-btn admin-btn-primary">{{ __('Kaydet') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
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
                                    @elseif (($item['type'] ?? 'text') === 'color')
                                        @php($colorValue = old('settings.'.$item['key'], $item['value'] ?: '#0071e3'))
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="color"
                                                name="settings[{{ $item['key'] }}]"
                                                value="{{ $colorValue }}"
                                                class="h-11 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700"
                                            >
                                            <span class="font-mono text-xs text-gray-500">{{ $colorValue }}</span>
                                        </div>
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
                    @endif
                </section>
            @endforeach

        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">{{ __('Save Settings') }}</button>
        </div>
    </form>
@endsection
