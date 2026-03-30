@php($isEdit = isset($link))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Slug (optional)</span>
        <input name="slug" class="form-input-admin" value="{{ old('slug', $link->slug ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Original Name</span>
        <input name="original_name" class="form-input-admin" value="{{ old('original_name', $link->original_name ?? '') }}" required>
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">File Path / URL</span>
        <input name="file_path" class="form-input-admin" value="{{ old('file_path', $link->file_path ?? '') }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">MIME Type</span>
        <input name="mime_type" class="form-input-admin" value="{{ old('mime_type', $link->mime_type ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Size (bytes)</span>
        <input type="number" min="0" name="size" class="form-input-admin" value="{{ old('size', $link->size ?? '') }}">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Expires At</span>
        <input type="datetime-local" name="expires_at" class="form-input-admin" value="{{ old('expires_at', isset($link) && $link->expires_at ? $link->expires_at->format('Y-m-d\TH:i') : '') }}">
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">{{ $isEdit ? 'Update Link' : 'Create Link' }}</button>
    <a href="{{ route('admin.links.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">Cancel</a>
</div>