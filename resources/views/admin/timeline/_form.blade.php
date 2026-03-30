@php($isEdit = isset($event))

<div class="grid gap-4 md:grid-cols-2">
    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Title</span>
        <input name="title" class="form-input-admin" value="{{ old('title', $event->title ?? '') }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Event Type</span>
        <select name="event_type" class="form-input-admin">
            @foreach (['milestone', 'process'] as $type)
                <option value="{{ $type }}" @selected(old('event_type', $event->event_type ?? 'milestone') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Color</span>
        <input name="color" class="form-input-admin" value="{{ old('color', $event->color ?? '#3B82F6') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Start Date</span>
        <input type="date" name="start_date" class="form-input-admin" value="{{ old('start_date', optional($event->start_date ?? null)->toDateString()) }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">End Date</span>
        <input type="date" name="end_date" class="form-input-admin" value="{{ old('end_date', optional($event->end_date ?? null)->toDateString()) }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Category</span>
        <input name="category" class="form-input-admin" value="{{ old('category', $event->category ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Location</span>
        <input name="location" class="form-input-admin" value="{{ old('location', $event->location ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Image Path</span>
        <input name="image" class="form-input-admin" value="{{ old('image', $event->image ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Icon</span>
        <input name="icon" class="form-input-admin" value="{{ old('icon', $event->icon ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Order</span>
        <input type="number" name="order" class="form-input-admin" value="{{ old('order', $event->order ?? 0) }}">
    </label>

    <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $event->is_public ?? true))>
        Public Event
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Description</span>
        <textarea name="description" class="form-input-admin min-h-24">{{ old('description', $event->description ?? '') }}</textarea>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Tags (comma separated)</span>
        <input name="tags" class="form-input-admin" value="{{ old('tags', isset($event) && is_array($event->tags) ? implode(', ', $event->tags) : '') }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Metadata (JSON)</span>
        <textarea name="metadata" class="form-input-admin min-h-24">{{ old('metadata', isset($event) && is_array($event->metadata) ? json_encode($event->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">{{ $isEdit ? 'Update Event' : 'Create Event' }}</button>
    <a href="{{ route('admin.timeline.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">Cancel</a>
</div>