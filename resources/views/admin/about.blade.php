@extends('admin.layout', ['title' => 'About Page', 'heading' => 'About Page'])

@section('content')
    <form method="POST" action="{{ route('admin.about.update') }}" class="space-y-6">
        @csrf

        <div x-data="{ tab: 'en' }" class="space-y-6">

            {{-- Tab switcher --}}
            <div class="flex gap-1 rounded-xl glass-well p-1 w-fit dark:border-white/10 dark:bg-white/[0.06]">
                <button type="button"
                    @click="tab = 'en'"
                    :class="tab === 'en' ? 'bg-[var(--glass-strong)] shadow-sm text-ink' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="rounded-lg px-5 py-2 text-sm font-medium transition">
                    🇬🇧 English
                </button>
                <button type="button"
                    @click="tab = 'tr'"
                    :class="tab === 'tr' ? 'bg-[var(--glass-strong)] shadow-sm text-ink' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="rounded-lg px-5 py-2 text-sm font-medium transition">
                    🇹🇷 Türkçe
                </button>
            </div>

            {{-- English Editor --}}
            <section class="card-admin p-6" x-show="tab === 'en'">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ __('English Content') }}</h2>
                <div
                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('content_en', $content_en)) }} })"
                    x-init="init()"
                    class="space-y-3"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Bold') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Italic') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                            class="admin-btn-sm admin-btn-ghost">H2</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                            class="admin-btn-sm admin-btn-ghost">H3</button>
                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                            class="admin-btn-sm admin-btn-ghost">• {{ __('List') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                            class="admin-btn-sm admin-btn-ghost">1. {{ __('List') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Quote') }}</button>
                        <button type="button" @click="editor?.chain().focus().undo().run()"
                            class="admin-btn-sm admin-btn-ghost">↩ {{ __('Undo') }}</button>
                        <button type="button" @click="editor?.chain().focus().redo().run()"
                            class="admin-btn-sm admin-btn-ghost">↪ {{ __('Redo') }}</button>
                    </div>
                    <div x-ref="editor"
                        class="prose prose-sm min-h-64 max-w-none rounded-lg border border-[var(--field-line)] bg-[var(--field)] px-4 py-3 text-sm text-ink">
                    </div>
                    <textarea name="content_en" x-model="content" class="form-input-admin hidden"></textarea>
                </div>
            </section>

            {{-- Turkish Editor --}}
            <section class="card-admin p-6" x-show="tab === 'tr'" x-cloak>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ __('Turkish Content') }}</h2>
                <div
                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('content_tr', $content_tr)) }} })"
                    x-init="init()"
                    class="space-y-3"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Bold') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Italic') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                            class="admin-btn-sm admin-btn-ghost">H2</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                            class="admin-btn-sm admin-btn-ghost">H3</button>
                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                            class="admin-btn-sm admin-btn-ghost">• {{ __('List') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                            class="admin-btn-sm admin-btn-ghost">1. {{ __('List') }}</button>
                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                            class="admin-btn-sm admin-btn-ghost">{{ __('Quote') }}</button>
                        <button type="button" @click="editor?.chain().focus().undo().run()"
                            class="admin-btn-sm admin-btn-ghost">↩ {{ __('Undo') }}</button>
                        <button type="button" @click="editor?.chain().focus().redo().run()"
                            class="admin-btn-sm admin-btn-ghost">↪ {{ __('Redo') }}</button>
                    </div>
                    <div x-ref="editor"
                        class="prose prose-sm min-h-64 max-w-none rounded-lg border border-[var(--field-line)] bg-[var(--field)] px-4 py-3 text-sm text-ink">
                    </div>
                    <textarea name="content_tr" x-model="content" class="form-input-admin hidden"></textarea>
                </div>
            </section>

        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="admin-btn admin-btn-primary px-6">
                {{ __('Save Changes') }}
            </button>
            <a href="{{ url('/about') }}" target="_blank"
                class="text-sm text-gray-500 underline underline-offset-4 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                {{ __('Preview') }} →
            </a>
        </div>
    </form>
@endsection
