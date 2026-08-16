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
                <div class="flex gap-2">
                    <a href="{{ route('admin.interactions.create') }}" class="admin-btn admin-btn-primary">{{ __('New Interaction') }}</a>
                    <a href="{{ route('admin.interactions.women-in-circle') }}" class="admin-btn bg-red-600 text-white hover:bg-red-700">{{ __('Women In Circle') }}</a>
                </div>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden w-12 sm:table-cell"><span class="sr-only">{{ __('Photo') }}</span></th>
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
                                <td class="hidden sm:table-cell">
                                    <img src="{{ optional($interaction->person)->picture_url }}" alt="" loading="lazy" class="h-8 w-8 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)]">
                                </td>
                                <td>{{ optional($interaction->date)->toDateString() }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <img src="{{ optional($interaction->person)->picture_url }}" alt="" loading="lazy" class="h-7 w-7 shrink-0 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)] sm:hidden">
                                        <span>{{ optional($interaction->person)->name }} {{ optional($interaction->person)->surname }}</span>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell">{{ optional($interaction->type)->name ?: '-' }}</td>
                                <td class="hidden md:table-cell">{{ $interaction->effect ?: '-' }}</td>
                                <td class="hidden max-w-sm text-xs lg:table-cell">{{ \Illuminate\Support\Str::limit($interaction->notes, 100) ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.interactions.edit', $interaction) }}" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 0 1 2.97 2.97L7.5 18.79 3 20l1.21-4.5L16.862 3.487Z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.interactions.duplicate', $interaction) }}">
                                            @csrf
                                            <button type="submit" title="{{ __('Duplicate') }}" aria-label="{{ __('Duplicate') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="9" y="9" width="12" height="12" rx="2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.interactions.destroy', $interaction) }}" data-confirm="{{ __('Delete this interaction?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}" class="admin-btn-sm admin-btn-danger w-8 p-0">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-8 0v14m8-14v14M5 6l1 14h12l1-14"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No interactions found.') }}</td>
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
