<div class="space-y-5">
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
</div>
