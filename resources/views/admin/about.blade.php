@extends('admin.layout', ['title' => 'About Page', 'heading' => 'About Page'])

@section('content')
    <form method="POST" action="{{ route('admin.about.update') }}" class="space-y-6">
        @csrf

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div x-data="{ tab: 'en' }" class="space-y-6">

            {{-- Tab switcher --}}
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

            {{-- English Editor --}}
            <section class="card-admin p-6" x-show="tab === 'en'">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">English Content</h2>
                <div
                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('content_en', $content_en)) }} })"
                    x-init="init()"
                    class="space-y-3"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Bold</button>
                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Italic</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H2</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H3</button>
                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">• List</button>
                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">1. List</button>
                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Quote</button>
                        <button type="button" @click="editor?.chain().focus().undo().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↩ Undo</button>
                        <button type="button" @click="editor?.chain().focus().redo().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↪ Redo</button>
                    </div>
                    <div x-ref="editor"
                        class="prose prose-sm min-h-64 max-w-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <textarea name="content_en" x-model="content" class="form-input-admin hidden"></textarea>
                </div>
            </section>

            {{-- Turkish Editor --}}
            <section class="card-admin p-6" x-show="tab === 'tr'" x-cloak>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">Türkçe İçerik</h2>
                <div
                    x-data="tiptapSimpleEditor({ content: {{ Js::from(old('content_tr', $content_tr)) }} })"
                    x-init="init()"
                    class="space-y-3"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Bold</button>
                        <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Italic</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H2</button>
                        <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">H3</button>
                        <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">• List</button>
                        <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">1. List</button>
                        <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">Quote</button>
                        <button type="button" @click="editor?.chain().focus().undo().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↩ Undo</button>
                        <button type="button" @click="editor?.chain().focus().redo().run()"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold dark:border-gray-700">↪ Redo</button>
                    </div>
                    <div x-ref="editor"
                        class="prose prose-sm min-h-64 max-w-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <textarea name="content_tr" x-model="content" class="form-input-admin hidden"></textarea>
                </div>
            </section>

        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="rounded-md bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
                Save Changes
            </button>
            <a href="{{ url('/about') }}" target="_blank"
                class="text-sm text-gray-500 underline underline-offset-4 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Preview →
            </a>
        </div>
    </form>
@endsection
