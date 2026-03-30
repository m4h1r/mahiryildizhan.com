@php($isEdit = isset($node))

<div class="grid gap-4 md:grid-cols-2">
    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Name</span>
        <input name="name" class="form-input-admin" value="{{ old('name', $node->name ?? '') }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Text Color</span>
        <input name="text_color" class="form-input-admin" value="{{ old('text_color', $node->text_color ?? '') }}" placeholder="#111827">
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Text Size</span>
        <input name="text_size" class="form-input-admin" value="{{ old('text_size', $node->text_size ?? '') }}" placeholder="18">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Image Path</span>
        <input name="image" class="form-input-admin" value="{{ old('image', $node->image ?? '') }}" placeholder="nodes/example.webp">
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? 'Update Node' : 'Create Node' }}
    </button>

    <a href="{{ route('admin.nodes.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        Cancel
    </a>
</div>