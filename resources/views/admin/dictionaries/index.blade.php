@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dictionary Management</p>
                    <h2 class="text-xl font-semibold">{{ $definition['label'] }}</h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $records->count() }} records</p>
            </div>

            <form method="POST" action="{{ route('admin.dictionaries.store', $table) }}" class="grid gap-4 md:grid-cols-2">
                @csrf

                @foreach ($definition['fields'] as $field => $meta)
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 md:col-span-{{ ($meta['type'] ?? 'text') === 'textarea' ? '2' : '1' }}">
                        <span class="mb-1 block">{{ $meta['label'] }}</span>

                        @if (($meta['type'] ?? 'text') === 'textarea')
                            <textarea
                                name="{{ $field }}"
                                rows="4"
                                class="form-input-admin min-h-28"
                            >{{ old($field) }}</textarea>
                        @elseif (($meta['type'] ?? 'text') === 'number')
                            <input
                                type="number"
                                name="{{ $field }}"
                                value="{{ old($field, $meta['default'] ?? null) }}"
                                @if(isset($meta['min'])) min="{{ $meta['min'] }}" @endif
                                @if(isset($meta['max'])) max="{{ $meta['max'] }}" @endif
                                @if(isset($meta['step'])) step="{{ $meta['step'] }}" @endif
                                class="form-input-admin"
                            >
                        @else
                            <input
                                type="text"
                                name="{{ $field }}"
                                value="{{ old($field) }}"
                                class="form-input-admin"
                            >
                        @endif

                        @error($field)
                            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </label>
                @endforeach

                <div class="md:col-span-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                        Add Entry
                    </button>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            @foreach ($definition['fields'] as $meta)
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">{{ $meta['label'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($records as $record)
                            <tr class="align-top">
                                @foreach ($definition['fields'] as $field => $meta)
                                    <td class="px-4 py-3">
                                        @if (($meta['type'] ?? 'text') === 'textarea')
                                            <textarea
                                                name="{{ $field }}"
                                                rows="3"
                                                form="dictionary-form-{{ $record->id }}"
                                                class="form-input-admin min-h-24"
                                            >{{ old($field.'.'.$record->id, $record->{$field}) }}</textarea>
                                        @elseif (($meta['type'] ?? 'text') === 'number')
                                            <input
                                                type="number"
                                                name="{{ $field }}"
                                                value="{{ old($field.'.'.$record->id, $record->{$field}) }}"
                                                form="dictionary-form-{{ $record->id }}"
                                                @if(isset($meta['min'])) min="{{ $meta['min'] }}" @endif
                                                @if(isset($meta['max'])) max="{{ $meta['max'] }}" @endif
                                                @if(isset($meta['step'])) step="{{ $meta['step'] }}" @endif
                                                class="form-input-admin"
                                            >
                                        @else
                                            <input
                                                type="text"
                                                name="{{ $field }}"
                                                value="{{ old($field.'.'.$record->id, $record->{$field}) }}"
                                                form="dictionary-form-{{ $record->id }}"
                                                class="form-input-admin"
                                            >
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-4 py-3">
                                    <form id="dictionary-form-{{ $record->id }}" method="POST" action="{{ route('admin.dictionaries.update', [$table, $record->id]) }}" class="hidden">
                                        @csrf
                                        @method('PUT')
                                    </form>

                                    <div class="flex justify-end gap-2">
                                        <button type="submit" form="dictionary-form-{{ $record->id }}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">
                                            Save
                                        </button>

                                        <form method="POST" action="{{ route('admin.dictionaries.destroy', [$table, $record->id]) }}" onsubmit="return confirm('Delete this entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($definition['fields']) + 1 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No entries yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
