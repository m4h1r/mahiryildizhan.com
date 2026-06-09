@php($isEdit = isset($person))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Name') }}</span>
        <input name="name" class="form-input-admin" value="{{ old('name', $person->name ?? '') }}" required>
        @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Surname') }}</span>
        <input name="surname" class="form-input-admin" value="{{ old('surname', $person->surname ?? '') }}" required>
        @error('surname')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Second Surname') }}</span>
        <input name="second_surname" class="form-input-admin" value="{{ old('second_surname', $person->second_surname ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Gender') }}</span>
        <select name="gender_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($genders as $gender)
                <option value="{{ $gender->id }}" @selected((string) old('gender_id', $person->gender_id ?? '') === (string) $gender->id)>{{ $gender->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Birthday') }}</span>
        <input type="date" name="birthday" class="form-input-admin" value="{{ old('birthday', optional($person->birthday ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Deathday') }}</span>
        <input type="date" name="deathday" class="form-input-admin" value="{{ old('deathday', optional($person->deathday ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Birth Place') }}</span>
        <input name="birth_place" class="form-input-admin" value="{{ old('birth_place', $person->birth_place ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Death Place') }}</span>
        <input name="death_place" class="form-input-admin" value="{{ old('death_place', $person->death_place ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Father') }}</span>
        <select name="father_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('father_id', $person->father_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }} {{ optional($candidate->birthday)->format('Y') ? '('.optional($candidate->birthday)->format('Y').')' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Mother') }}</span>
        <select name="mother_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('mother_id', $person->mother_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }} {{ optional($candidate->birthday)->format('Y') ? '('.optional($candidate->birthday)->format('Y').')' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Partner') }}</span>
        <select name="partner_id" class="form-input-admin">
            <option value="">{{ __('None') }}</option>
            @foreach ($peopleOptions as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('partner_id', $person->partner_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->surname }}, {{ $candidate->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Eye Color') }}</span>
        <select name="eye_color_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($eyeColors as $eyeColor)
                <option value="{{ $eyeColor->id }}" @selected((string) old('eye_color_id', $person->eye_color_id ?? '') === (string) $eyeColor->id)>{{ $eyeColor->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Blood Type') }}</span>
        <select name="blood_type_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($bloodTypes as $bloodType)
                <option value="{{ $bloodType->id }}" @selected((string) old('blood_type_id', $person->blood_type_id ?? '') === (string) $bloodType->id)>{{ $bloodType->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Hair Color') }}</span>
        <select name="hair_color_id" class="form-input-admin">
            <option value="">{{ __('Unknown') }}</option>
            @foreach ($hairColors as $hairColor)
                <option value="{{ $hairColor->id }}" @selected((string) old('hair_color_id', $person->hair_color_id ?? '') === (string) $hairColor->id)>{{ $hairColor->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Phone') }}</span>
        <input name="mobile" class="form-input-admin" value="{{ old('mobile', $person->mobile ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Email') }}</span>
        <input name="email" type="email" class="form-input-admin" value="{{ old('email', $person->email ?? '') }}">
    </label>

    <div
        class="md:col-span-2"
        x-data="{
            picture: {{ Js::from(old('picture', isset($person) && $person->picture ? $person->pictureUrl : '')) }},
            showPicker: false,
            mediaItems: [],
            libraryMeta: { page: 1, last_page: 1 },
            loadingLibrary: false,
            libraryQuery: '',
            libraryType: '1',
            searchDebounceTimer: null,
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
                    window.alert(e?.response?.data?.message ?? 'Media library could not be loaded.');
                } finally {
                    this.loadingLibrary = false;
                }
            },
            selectMedia(item) {
                this.picture = item.original_url || item.url || '';
                this.showPicker = false;
            }
        }"
    >
        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Picture') }}</p>

        {{-- Hidden input submitted with the form --}}
        <input type="hidden" name="picture" :value="picture">

        {{-- Current preview + clear --}}
        <div class="mb-2 flex items-center gap-3">
            <template x-if="picture">
                <div class="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <img :src="picture" class="h-full w-full object-cover">
                    <button
                        type="button"
                        @click="picture = ''"
                        class="absolute right-0.5 top-0.5 flex h-5 w-5 items-center justify-center rounded bg-black/60 text-white hover:bg-black/80"
                        title="Remove"
                    >&times;</button>
                </div>
            </template>
            <template x-if="!picture">
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-lg border border-dashed border-gray-300 text-gray-400 dark:border-gray-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </template>
            <button
                type="button"
                @click="openPicker()"
                class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700"
            >{{ __('Choose from Media Library') }}</button>
        </div>

        {{-- Media library panel --}}
        <div x-show="showPicker" x-cloak class="rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
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
                <button type="button" @click="fetchLibrary(1)" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">{{ __('Refresh') }}</button>
            </div>
            <p x-show="loadingLibrary" class="text-xs text-gray-500">{{ __('Loading media...') }}</p>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="item in mediaItems" :key="item.id">
                    <button
                        type="button"
                        @click="selectMedia(item)"
                        class="rounded-md border border-gray-200 p-2 text-left transition-colors hover:border-blue-400 dark:border-gray-700"
                    >
                        <img :src="item.thumbnail_url" :alt="item.alt || item.filename" class="aspect-video w-full rounded object-cover">
                        <span class="mt-1 block truncate text-xs" x-text="item.filename"></span>
                        <span class="block text-[10px] text-gray-500" x-text="(item.width && item.height) ? `${item.width}×${item.height}` : (item.mime_type || '')"></span>
                    </button>
                </template>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500" x-show="libraryMeta.last_page > 1">
                <button type="button" @click="fetchLibrary(libraryMeta.page - 1)" :disabled="libraryMeta.page <= 1" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Prev') }}</button>
                <span x-text="`Page ${libraryMeta.page} / ${libraryMeta.last_page}`"></span>
                <button type="button" @click="fetchLibrary(libraryMeta.page + 1)" :disabled="libraryMeta.page >= libraryMeta.last_page" class="rounded-md border border-gray-300 px-2 py-1 disabled:opacity-50 dark:border-gray-700">{{ __('Next') }}</button>
            </div>
        </div>
    </div>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Address') }}</span>
        <textarea name="address" class="form-input-admin min-h-20" rows="3">{{ old('address', $person->address ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Notes') }}</span>
        <textarea name="notes" class="form-input-admin min-h-28">{{ old('notes', $person->notes ?? '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Person') : __('Create Person') }}
    </button>

    <a href="{{ route('admin.people.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>
