{{-- Profile picture picker (media library or upload). Public image — face samples for recognition live in the private Face data tab. --}}
    <div
        class=""
        x-data="{
            picture: {{ Js::from(old('picture', isset($person) && $person->picture ? $person->pictureUrl : '')) }},
            tab: 'library',
            showPicker: false,
            mediaItems: [],
            libraryMeta: { page: 1, last_page: 1 },
            loadingLibrary: false,
            libraryQuery: '',
            libraryType: '1',
            searchDebounceTimer: null,
            localPreview: null,
            mediaLibraryUrl: '{{ route('admin.media.library') }}',
            async openPicker() {
                this.showPicker = true;
                if (this.mediaItems.length === 0) {
                    await this.fetchLibrary(1);
                }
            },
            debouncedSearch() {
                window.clearTimeout(this.searchDebounceTimer);
                this.searchDebounceTimer = window.setTimeout(() => { this.fetchLibrary(1); }, 250);
            },
            async fetchLibrary(page = 1) {
                this.loadingLibrary = true;
                try {
                    const resp = await window.axios.get(this.mediaLibraryUrl, {
                        params: { q: this.libraryQuery || undefined, type: this.libraryType || undefined, page, per_page: 24 }
                    });
                    this.mediaItems = resp?.data?.data ?? [];
                    this.libraryMeta = resp?.data?.meta ?? this.libraryMeta;
                } catch (e) {
                    await window.appDialog.alert(e?.response?.data?.message ?? 'Media library could not be loaded.');
                } finally {
                    this.loadingLibrary = false;
                }
            },
            selectMedia(item) {
                this.picture = item.original_url || item.url || '';
                this.localPreview = null;
                this.$refs.fileInput.value = '';
                this.showPicker = false;
            },
            handleFileSelect(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => { this.localPreview = e.target.result; this.picture = ''; };
                reader.readAsDataURL(file);
            },
            clearImage() {
                this.picture = '';
                this.localPreview = null;
                this.$refs.fileInput.value = '';
            },
            get previewSrc() {
                return this.localPreview || this.picture || null;
            }
        }"
    >
        <p class="mb-1 text-sm font-medium text-ink-soft">{{ __('Profile picture') }}</p>

        {{-- Hidden input submitted with the form --}}
        <input type="hidden" name="picture" :value="picture">

        {{-- File input (always in DOM for form submission) --}}
        <input
            type="file"
            name="picture_file"
            accept="image/*"
            x-ref="fileInput"
            @change="handleFileSelect($event)"
            class="sr-only"
        >

        {{-- Current preview + clear --}}
        <div class="mb-2 flex items-center gap-3">
            <template x-if="previewSrc">
                <div class="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-glass dark:border-white/10">
                    <img :src="previewSrc" class="h-full w-full object-cover">
                    <button
                        type="button"
                        @click="clearImage()"
                        class="absolute right-0.5 top-0.5 flex h-5 w-5 items-center justify-center rounded bg-black/60 text-white hover:bg-black/80"
                        title="Remove"
                    >&times;</button>
                </div>
            </template>
            <template x-if="!previewSrc">
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-lg border border-dashed border-[var(--field-line)] text-gray-400 dark:border-white/10">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </template>
            <div class="flex min-w-0 flex-col gap-1.5">
                <div class="flex flex-wrap gap-1">
                    <button
                        type="button"
                        @click="tab = 'library'; openPicker()"
                        :class="tab === 'library' ? 'admin-btn-primary' : 'admin-btn-ghost'"
                        class="admin-btn-sm"
                    >{{ __('Media Library') }}</button>
                    <button
                        type="button"
                        @click="tab = 'upload'; $refs.fileInput.click()"
                        :class="tab === 'upload' ? 'admin-btn-primary' : 'admin-btn-ghost'"
                        class="admin-btn-sm"
                    >{{ __('Upload from Computer') }}</button>
                </div>
                <p x-show="localPreview" class="text-[11px] text-emerald-600 dark:text-emerald-400">{{ __('File selected — will upload on save.') }}</p>
            </div>
        </div>

        {{-- Media library panel --}}
        <div x-show="showPicker" x-cloak class="rounded-xl glass-well p-3">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-xs font-semibold">{{ __('Select from media library') }}</p>
                <button type="button" @click="showPicker = false" class="text-xs text-gray-500">{{ __('Close') }}</button>
            </div>
            <div class="mb-3 grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                <input
                    type="text"
                    x-model="libraryQuery"
                    @input="debouncedSearch()"
                    class="form-input-admin"
                    placeholder="{{ __('Search by filename, alt or caption...') }}"
                >
                <select x-model="libraryType" @change="fetchLibrary(1)" class="form-input-admin">
                    <option value="1">{{ __('Images') }}</option>
                    <option value="2">{{ __('Documents') }}</option>
                    <option value="">{{ __('All') }}</option>
                </select>
                <button type="button" @click="fetchLibrary(1)" class="admin-btn-sm admin-btn-ghost">{{ __('Refresh') }}</button>
            </div>
            <p x-show="loadingLibrary" class="text-xs text-gray-500">{{ __('Loading media...') }}</p>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="item in mediaItems" :key="item.id">
                    <button
                        type="button"
                        @click="selectMedia(item)"
                        class="rounded-md border border-glass p-2 text-left transition-colors hover:border-blue-400 dark:border-white/10"
                    >
                        <img :src="item.thumbnail_url" :alt="item.alt || item.filename" class="aspect-video w-full rounded object-cover">
                        <span class="mt-1 block truncate text-xs" x-text="item.filename"></span>
                        <span class="block text-[10px] text-gray-500" x-text="(item.width && item.height) ? `${item.width}×${item.height}` : (item.mime_type || '')"></span>
                    </button>
                </template>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500" x-show="libraryMeta.last_page > 1">
                <button type="button" @click="fetchLibrary(libraryMeta.page - 1)" :disabled="libraryMeta.page <= 1" class="admin-btn-sm admin-btn-ghost">{{ __('Prev') }}</button>
                <span x-text="`Page ${libraryMeta.page} / ${libraryMeta.last_page}`"></span>
                <button type="button" @click="fetchLibrary(libraryMeta.page + 1)" :disabled="libraryMeta.page >= libraryMeta.last_page" class="admin-btn-sm admin-btn-ghost">{{ __('Next') }}</button>
            </div>
        </div>
    </div>

