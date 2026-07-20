@php($isEdit = isset($post))

<div class="grid gap-4 md:grid-cols-2">
    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Title') }}</span>
        <input name="title" class="form-input-admin" value="{{ old('title', $post->title ?? '') }}" required>
        @error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Slug (optional)') }}</span>
        <input name="slug" class="form-input-admin" value="{{ old('slug', $post->slug ?? '') }}" placeholder="auto-generated from title">
        @error('slug')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Excerpt') }}</span>
        <textarea name="excerpt" class="form-input-admin min-h-24" placeholder="{{ __('Leave empty to auto-generate from body') }}">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Body') }}</span>
        <div
            x-data="tiptapPostEditor({
                content: {{ Js::from(old('body', $post->body ?? '')) }},
                uploadUrl: '{{ route('admin.posts.upload-image') }}',
                mediaLibraryUrl: '{{ route('admin.media.library') }}',
                csrfToken: '{{ csrf_token() }}'
            })"
            x-init="init()"
            class="space-y-3"
        >
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="editor?.chain().focus().toggleBold().run()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Bold') }}</button>
                <button type="button" @click="editor?.chain().focus().toggleItalic().run()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Italic') }}</button>
                <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">H2</button>
                <button type="button" @click="editor?.chain().focus().toggleBulletList().run()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('List') }}</button>
                <button type="button" @click="triggerImagePicker()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Image') }}</button>
                <button type="button" @click="openMediaLibrary()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Media Library') }}</button>
                <button type="button" @click="insertYoutubeVideo()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('YouTube') }}</button>
                <span x-show="loadingImage" x-cloak class="text-xs text-gray-500">{{ __('Uploading image...') }}</span>
            </div>

            <input x-ref="imageInput" type="file" class="hidden" accept="image/*" @change="uploadInlineImage($event)">
            <div x-ref="editor" class="min-h-56 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-600 dark:bg-gray-800"></div>
            <textarea name="body" x-model="content" class="form-input-admin min-h-56" required></textarea>
            <p class="text-xs text-gray-500">{{ __('The textarea remains as a no-JS fallback and mirrors the editor HTML content.') }}</p>

            <div x-show="showMediaLibrary" x-cloak class="rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-xs font-semibold">{{ __('Select from media library') }}</p>
                    <button type="button" @click="showMediaLibrary = false" class="text-xs text-gray-500">{{ __('Close') }}</button>
                </div>
                <div class="mb-3 grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                    <input
                        type="text"
                        x-model="libraryQuery"
                        @input="debouncedLibrarySearch()"
                        class="form-input-admin"
                        placeholder="{{ __('Search by filename, alt or caption...') }}"
                    >
                    <select x-model="libraryType" @change="fetchMediaLibrary(1)" class="form-input-admin">
                        <option value="1">{{ __('Images') }}</option>
                        <option value="2">{{ __('Documents') }}</option>
                        <option value="">{{ __('All') }}</option>
                    </select>
                    <button type="button" @click="fetchMediaLibrary(1)" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Refresh') }}</button>
                </div>
                <p x-show="loadingLibrary" class="text-xs text-gray-500">{{ __('Loading media...') }}</p>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in mediaItems" :key="item.id">
                        <button type="button" @click="insertFromMedia(item)" class="rounded-md border border-gray-200 p-2 text-left dark:border-gray-700">
                            <img :src="item.thumbnail_url" :alt="item.alt || item.filename" class="aspect-video w-full rounded object-cover">
                            <span class="mt-1 block truncate text-xs" x-text="item.filename"></span>
                            <span class="block text-[10px] text-gray-500" x-text="(item.width && item.height) ? `${item.width}x${item.height}` : (item.mime_type || '')"></span>
                        </button>
                    </template>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-gray-500" x-show="libraryMeta.last_page > 1">
                    <button type="button" @click="fetchMediaLibrary(libraryMeta.page - 1)" :disabled="libraryMeta.page <= 1" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Prev') }}</button>
                    <span x-text="`Page ${libraryMeta.page} / ${libraryMeta.last_page}`"></span>
                    <button type="button" @click="fetchMediaLibrary(libraryMeta.page + 1)" :disabled="libraryMeta.page >= libraryMeta.last_page" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Next') }}</button>
                </div>
            </div>
        </div>
        @error('body')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Cover Image') }}</span>
        <input type="file" name="cover_upload" accept="image/*" class="form-input-admin">
        <span class="mt-2 block text-xs text-gray-500">{{ __('or choose from media library:') }}</span>
        <select name="cover_media_id" class="form-input-admin mt-2">
            <option value="">{{ __('None') }}</option>
            @foreach (($mediaItems ?? collect()) as $media)
                <option value="{{ $media->id }}" @selected((string) old('cover_media_id', $post->cover_media_id ?? '') === (string) $media->id)>
                    #{{ $media->id }} - {{ $media->filename }}
                </option>
            @endforeach
        </select>
        @if (! empty($post->cover ?? null))
            <span class="mt-2 block text-xs text-gray-500">{{ __('Current cover:') }} {{ $post->cover }}</span>
        @endif
        @error('cover_upload')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        @error('cover_media_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Category') }}</span>
        <select name="category_id" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Language') }}</span>
        <select name="language_id" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            @foreach ($languages as $language)
                <option value="{{ $language->id }}" @selected((string) old('language_id', $post->language_id ?? '') === (string) $language->id)>
                    {{ $language->name }} ({{ $language->code }})
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Status') }}</span>
        <select name="status" class="form-input-admin">
            @foreach (['draft', 'published', 'archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', $post->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Publish Date') }}</span>
        <input type="date" name="publish_date" class="form-input-admin" value="{{ old('publish_date', optional($post->publish_date ?? null)->toDateString()) }}">
    </label>

    <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
        <input type="checkbox" name="published" value="1" @checked(old('published', $post->published ?? false))>
        {{ __('Published') }}
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Canonical URL') }}</span>
        <input type="url" name="canonical_url" class="form-input-admin" value="{{ old('canonical_url', $post->canonical_url ?? '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('SEO Title') }}</span>
        <input name="seo_title" class="form-input-admin" value="{{ old('seo_title', $post->seo_title ?? '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('SEO Description') }}</span>
        <input name="seo_description" class="form-input-admin" value="{{ old('seo_description', $post->seo_description ?? '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('SEO Keywords') }}</span>
        <input name="seo_keywords" class="form-input-admin" value="{{ old('seo_keywords', $post->seo_keywords ?? '') }}">
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Post') : __('Create Post') }}
    </button>

    <a href="{{ route('admin.posts.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>
