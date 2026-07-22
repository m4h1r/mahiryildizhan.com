@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Dictionary Management') }}</p>
                    <h2 class="text-xl font-semibold">{{ $definition['label'] }}</h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $records->count() }} {{ __('records') }}</p>
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
                    <button type="submit" class="admin-btn admin-btn-primary">
                        {{ __('Add Entry') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            @foreach ($definition['fields'] as $meta)
                                <th>{{ $meta['label'] }}</th>
                            @endforeach
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
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

                                    <div class="flex justify-end gap-1.5">
                                        <button type="submit" form="dictionary-form-{{ $record->id }}" class="admin-btn-sm admin-btn-ghost">
                                            {{ __('Save') }}
                                        </button>

                                        <form method="POST" action="{{ route('admin.dictionaries.destroy', [$table, $record->id]) }}" data-confirm="{{ __('Delete this entry?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($definition['fields']) + 1 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('No entries yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
