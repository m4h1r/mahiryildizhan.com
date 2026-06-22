@extends('admin.layout', ['title' => 'Interactions', 'heading' => 'Interactions'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2 lg:col-span-1" name="q" placeholder="{{ __('Search notes or person...') }}" value="{{ $filters['q'] ?? '' }}">

                <select name="person_id" class="form-input-admin">
                    <option value="">{{ __('All People') }}</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}" @selected((string) ($filters['person_id'] ?? '') === (string) $person->id)>
                            {{ $person->name }} {{ $person->surname }}
                        </option>
                    @endforeach
                </select>

                <select name="interaction_type_id" class="form-input-admin">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((string) ($filters['interaction_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.interactions.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Interaction List') }}</h2>
                <a href="{{ route('admin.interactions.create') }}" class="admin-btn admin-btn-primary">{{ __('New Interaction') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Person') }}</th>
                            <th class="hidden sm:table-cell">{{ __('Type') }}</th>
                            <th class="hidden md:table-cell">{{ __('Effect') }}</th>
                            <th class="hidden lg:table-cell">{{ __('Notes') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($interactions as $interaction)
                            <tr>
                                <td>{{ optional($interaction->date)->toDateString() }}</td>
                                <td>{{ optional($interaction->person)->name }} {{ optional($interaction->person)->surname }}</td>
                                <td class="hidden sm:table-cell">{{ optional($interaction->type)->name ?: '-' }}</td>
                                <td class="hidden md:table-cell">{{ $interaction->effect ?: '-' }}</td>
                                <td class="hidden max-w-sm text-xs lg:table-cell">{{ \Illuminate\Support\Str::limit($interaction->notes, 100) ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.interactions.edit', $interaction) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>

                                        <form method="POST" action="{{ route('admin.interactions.duplicate', $interaction) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn-sm admin-btn-ghost">{{ __('Duplicate') }}</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.interactions.destroy', $interaction) }}" onsubmit="return confirm('{{ __('Delete this interaction?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No interactions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $interactions->links() }}
            </div>
        </section>
    </div>
@endsection
