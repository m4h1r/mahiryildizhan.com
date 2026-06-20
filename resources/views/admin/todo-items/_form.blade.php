<div class="space-y-5" x-data="{ isBucketlist: {{ ($item->is_bucketlist ?? false) ? 'true' : 'false' }} }">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Title') }} <span class="text-red-500">*</span></span>
        <input
            type="text"
            name="title"
            class="form-input-admin"
            value="{{ old('title', $item->title ?? '') }}"
            required
            maxlength="255"
            autofocus
        >
        @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </label>

    <div x-data="{ chars: {{ strlen(old('description', $item->description ?? '')) }} }">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 flex items-center justify-between">
                <span>{{ __('Description') }}</span>
                <span class="text-xs" :class="chars > 450 ? 'text-orange-500' : 'text-gray-400'">
                    <span x-text="chars"></span>/500
                </span>
            </span>
            <textarea
                name="description"
                rows="3"
                class="form-input-admin"
                maxlength="500"
                @input="chars = $event.target.value.length"
            >{{ old('description', $item->description ?? '') }}</textarea>
        </label>
        @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Due Date') }}</span>
        <input
            type="date"
            name="due_date"
            class="form-input-admin"
            value="{{ old('due_date', isset($item->due_date) ? $item->due_date->format('Y-m-d') : '') }}"
        >
        @error('due_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </label>

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('Yearly Goal') }}</span>
        @php
            $selectedYearlyGoal = (string) old('yearly_goal', $item->yearly_goal ?? 'NA');
            $yearStart = now()->year - 1;
            $yearEnd = now()->year + 5;
        @endphp
        <select name="yearly_goal" class="form-input-admin">
            <option value="NA" {{ $selectedYearlyGoal === 'NA' ? 'selected' : '' }}>NA</option>
            @if ($selectedYearlyGoal !== 'NA' && ((int) $selectedYearlyGoal < $yearStart || (int) $selectedYearlyGoal > $yearEnd))
                <option value="{{ $selectedYearlyGoal }}" selected>{{ $selectedYearlyGoal }}</option>
            @endif
            @for ($y = $yearStart; $y <= $yearEnd; $y++)
                <option value="{{ $y }}" {{ $selectedYearlyGoal === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        @error('yearly_goal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </label>

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">{{ __('Cost') }} (₺)</span>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">₺</span>
                <input
                    type="number"
                    name="cost_try"
                    class="form-input-admin pl-7"
                    value="{{ old('cost_try', $item->cost_try ?? '') }}"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                >
            </div>
            @error('cost_try')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </label>

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">{{ __('Time Cost') }}</span>
            <div class="relative">
                <input
                    type="number"
                    name="time_cost_hours"
                    class="form-input-admin pr-14"
                    value="{{ old('time_cost_hours', $item->time_cost_hours ?? '') }}"
                    step="0.5"
                    min="0"
                    placeholder="0"
                >
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">saat</span>
            </div>
            @error('time_cost_hours')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </label>
    </div>

    <div class="flex flex-wrap gap-6">
        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <input
                type="checkbox"
                name="is_bucketlist"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                {{ old('is_bucketlist', $item->is_bucketlist ?? false) ? 'checked' : '' }}
                @change="isBucketlist = $event.target.checked"
            >
            <span>Bucket List'e ekle</span>
        </label>

        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <input
                type="checkbox"
                name="is_completed"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                {{ old('is_completed', $item->is_completed ?? false) ? 'checked' : '' }}
            >
            <span>Tamamlandı</span>
        </label>
    </div>

    {{-- Bucket List resmi — sadece Bucket List seçiliyken göster --}}
    <div x-show="isBucketlist" x-transition class="space-y-3 rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-800/50 dark:bg-purple-950/20">
        <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">Tamamlanma Fotoğrafı</p>

        @if (!empty($item->image_path))
            <div class="flex items-start gap-3">
                <img src="{{ Storage::url($item->image_path) }}" alt="Mevcut fotoğraf"
                     class="h-24 w-24 rounded-lg object-cover ring-1 ring-purple-200 dark:ring-purple-700">
                <div class="space-y-1.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Mevcut fotoğraf</p>
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-red-500">
                        <input type="checkbox" name="remove_image" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-red-500">
                        Fotoğrafı sil
                    </label>
                </div>
            </div>
        @endif

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block text-xs">{{ empty($item->image_path) ? 'Fotoğraf yükle' : 'Yeni fotoğraf yükle (değiştirir)' }}</span>
            <input
                type="file"
                name="image"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-purple-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-purple-700 hover:file:bg-purple-200 dark:text-gray-400 dark:file:bg-purple-900/40 dark:file:text-purple-300"
            >
        </label>
        @error('image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        <p class="text-xs text-gray-400 dark:text-gray-500">Tamamlandığında bu fotoğraf kilometre taşı olarak kaydedilir. Maks. 4 MB.</p>
    </div>
</div>
