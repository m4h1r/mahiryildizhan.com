@php($isEdit = isset($connection))

<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('From Node') }}</span>
        <select name="node_from_id" class="form-input-admin" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($nodes as $node)
                <option value="{{ $node->id }}" @selected((string) old('node_from_id', $connection->node_from_id ?? '') === (string) $node->id)>{{ $node->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">{{ __('To Node') }}</span>
        <select name="node_to_id" class="form-input-admin" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($nodes as $node)
                <option value="{{ $node->id }}" @selected((string) old('node_to_id', $connection->node_to_id ?? '') === (string) $node->id)>{{ $node->name }}</option>
            @endforeach
        </select>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? __('Update Connection') : __('Create Connection') }}
    </button>

    <a href="{{ route('admin.node-connections.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        {{ __('Cancel') }}
    </a>
</div>