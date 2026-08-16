@php($isEdit = isset($event))

<div class="grid gap-4 md:grid-cols-2">
    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Title') }}</span>
        <input name="title" class="form-input-admin" value="{{ old('title', $event->title ?? '') }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Event Type') }}</span>
        <select name="event_type" class="form-input-admin">
            @foreach (['milestone', 'process'] as $type)
                <option value="{{ $type }}" @selected(old('event_type', $event->event_type ?? 'milestone') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" x-data="{ color: {{ Js::from(old('color', $event->color ?? '#3B82F6')) }} }">
        <span class="mb-1 block">{{ __('Color') }}</span>
        <div class="flex items-center gap-3">
            <input type="color" x-model="color" class="h-11 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700">
            <input type="text" name="color" x-model="color" class="form-input-admin font-mono" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
        </div>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Start Date') }}</span>
        <input type="date" name="start_date" class="form-input-admin" value="{{ old('start_date', optional($event->start_date ?? null)->toDateString()) }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('End Date') }}</span>
        <input type="date" name="end_date" class="form-input-admin" value="{{ old('end_date', optional($event->end_date ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Category') }}</span>
        <input name="category" class="form-input-admin" value="{{ old('category', $event->category ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Location') }}</span>
        <input name="location" class="form-input-admin" value="{{ old('location', $event->location ?? '') }}">
    </label>

    <div
        class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200"
        x-data="{
            imageUrl: {{ Js::from(old('image', $event->image ?? '')) }},
            showLibrary: false,
            uploading: false,
            loadingLibrary: false,
            libraryQuery: '',
            libraryPage: 1,
            libraryLastPage: 1,
            mediaItems: [],
            uploadJsonUrl: '{{ route('admin.media.upload-json') }}',
            libraryUrl: '{{ route('admin.media.library') }}',
            csrfToken: '{{ csrf_token() }}',

            async uploadFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.uploading = true;
                const form = new FormData();
                form.append('file', file);
                form.append('_token', this.csrfToken);
                try {
                    const res = await fetch(this.uploadJsonUrl, { method: 'POST', body: form });
                    const data = await res.json();
                    if (data.url) { this.imageUrl = data.url; }
                } catch (e) { alert('{{ __('Upload failed.') }}'); }
                this.uploading = false;
                event.target.value = '';
            },

            async fetchLibrary(page) {
                this.loadingLibrary = true;
                this.libraryPage = page || 1;
                const url = new URL(this.libraryUrl);
                url.searchParams.set('page', this.libraryPage);
                url.searchParams.set('type', 1);
                if (this.libraryQuery) url.searchParams.set('q', this.libraryQuery);
                const res = await fetch(url.toString());
                const data = await res.json();
                this.mediaItems = data.data || [];
                this.libraryLastPage = data.meta?.last_page || 1;
                this.loadingLibrary = false;
            },

            selectMedia(item) {
                this.imageUrl = item.url;
                this.showLibrary = false;
            },

            clearImage() {
                this.imageUrl = '';
            }
        }"
    >
        <span class="mb-1 block">{{ __('Image') }}</span>

        {{-- Hidden input carrying the value to form submission --}}
        <input type="hidden" name="image" :value="imageUrl">

        {{-- Preview --}}
        <div x-show="imageUrl" class="mb-3 flex items-start gap-3">
            <img :src="imageUrl" class="h-24 w-40 rounded-lg border border-gray-200 object-cover dark:border-gray-700" alt="{{ __('Selected image') }}">
            <button type="button" @click="clearImage()" class="mt-1 text-xs text-red-500 hover:underline">{{ __('Remove') }}</button>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            <label class="cursor-pointer rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                <span x-show="!uploading">{{ __('Upload new image') }}</span>
                <span x-show="uploading" x-cloak>{{ __('Uploading…') }}</span>
                <input type="file" accept="image/*" class="hidden" @change="uploadFile($event)" :disabled="uploading">
            </label>
            <button
                type="button"
                @click="showLibrary = !showLibrary; if(showLibrary && mediaItems.length === 0) fetchLibrary(1)"
                class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
            >
                <span x-text="showLibrary ? '{{ __('Close library') }}' : '{{ __('Select from library') }}'"></span>
            </button>
        </div>

        {{-- Library panel --}}
        <div x-show="showLibrary" x-cloak class="mt-3 rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-3 flex gap-2">
                <input
                    type="text"
                    x-model="libraryQuery"
                    @input.debounce.400ms="fetchLibrary(1)"
                    class="form-input-admin flex-1"
                    placeholder="{{ __('Search media…') }}"
                >
                <button type="button" @click="fetchLibrary(1)" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Refresh') }}</button>
            </div>
            <p x-show="loadingLibrary" class="text-xs text-gray-500">{{ __('Loading…') }}</p>
            <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4">
                <template x-for="item in mediaItems" :key="item.id">
                    <button
                        type="button"
                        @click="selectMedia(item)"
                        class="rounded-md border border-gray-200 p-2 text-left hover:border-blue-500 dark:border-gray-700"
                        :class="imageUrl === item.url ? 'ring-2 ring-blue-500' : ''"
                    >
                        <img :src="item.thumbnail_url" :alt="item.alt || item.filename" class="aspect-video w-full rounded object-cover">
                        <span class="mt-1 block truncate text-[10px] text-gray-500" x-text="item.filename"></span>
                    </button>
                </template>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500" x-show="libraryLastPage > 1">
                <button type="button" @click="fetchLibrary(libraryPage - 1)" :disabled="libraryPage <= 1" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Prev') }}</button>
                <span x-text="`Page ${libraryPage} / ${libraryLastPage}`"></span>
                <button type="button" @click="fetchLibrary(libraryPage + 1)" :disabled="libraryPage >= libraryLastPage" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Next') }}</button>
            </div>
        </div>
    </div>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Icon') }}</span>
        <input name="icon" class="form-input-admin" value="{{ old('icon', $event->icon ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Order') }}</span>
        <input type="number" name="order" class="form-input-admin" value="{{ old('order', $event->order ?? 0) }}">
    </label>

    <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $event->is_public ?? true))>
        {{ __('Public Event') }}
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Description') }}</span>
        <textarea name="description" class="form-input-admin min-h-24">{{ old('description', $event->description ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Tags (comma separated)') }}</span>
        <input name="tags" class="form-input-admin" value="{{ old('tags', isset($event) && is_array($event->tags) ? implode(', ', $event->tags) : '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Metadata (JSON)') }}</span>
        <textarea name="metadata" class="form-input-admin min-h-24">{{ old('metadata', isset($event) && is_array($event->metadata) ? json_encode($event->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">{{ $isEdit ? __('Update Event') : __('Create Event') }}</button>
    <a href="{{ route('admin.timeline.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">{{ __('Cancel') }}</a>
</div>