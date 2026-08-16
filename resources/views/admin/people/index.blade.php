@extends('admin.layout', ['title' => 'People', 'heading' => 'People'])

@section('content')
    @php
        $activeSort = $filters['sort'] ?? 'id';
        $activeDir = $filters['dir'] ?? 'asc';

        $nextDir = function (string $column) use ($activeSort, $activeDir): string {
            return ($activeSort === $column && $activeDir === 'asc') ? 'desc' : 'asc';
        };

        $sortArrow = function (string $column) use ($activeSort, $activeDir): string {
            if ($activeSort !== $column) {
                return '↕';
            }

            return $activeDir === 'asc' ? '↑' : '↓';
        };

        $sortUrl = function (string $column) use ($filters, $nextDir): string {
            return route('admin.people.index', array_merge($filters, [
                'sort' => $column,
                'dir' => $nextDir($column),
                'page' => null,
            ]));
        };
    @endphp

    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form id="people-filter-form" method="GET" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input
                    id="people-live-search"
                    name="q"
                    class="form-input-admin"
                    placeholder="{{ __('Search name, surname, maiden surname...') }}"
                    value="{{ $filters['q'] ?? '' }}"
                    autocomplete="off"
                >
                <input type="hidden" name="sort" value="{{ $activeSort }}">
                <input type="hidden" name="dir" value="{{ $activeDir }}">

                <select name="gender_id" class="form-input-admin">
                    <option value="">{{ __('All Genders') }}</option>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender->id }}" @selected((string) ($filters['gender_id'] ?? '') === (string) $gender->id)>{{ $gender->name }}</option>
                    @endforeach
                </select>

                <select name="alive" class="form-input-admin">
                    <option value="">{{ __('All Life States') }}</option>
                    <option value="1" @selected(($filters['alive'] ?? '') === '1')>{{ __('Alive') }}</option>
                    <option value="0" @selected(($filters['alive'] ?? '') === '0')>{{ __('Deceased') }}</option>
                </select>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.people.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('People List') }}</h2>
                <a href="{{ route('admin.people.create') }}" class="admin-btn admin-btn-primary">{{ __('New Person') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden w-14 sm:table-cell"><span class="sr-only">{{ __('Photo') }}</span></th>
                            <th class="hidden xl:table-cell">
                                <a href="{{ $sortUrl('id') }}" class="inline-flex items-center gap-1 hover:underline">ID <span>{{ $sortArrow('id') }}</span></a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Person') }} <span>{{ $sortArrow('name') }}</span></a>
                            </th>
                            <th class="hidden sm:table-cell">
                                <a href="{{ $sortUrl('surname') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Surname') }} <span>{{ $sortArrow('surname') }}</span></a>
                            </th>
                            <th class="hidden xl:table-cell">
                                <a href="{{ $sortUrl('second_surname') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Maiden Surname') }} <span>{{ $sortArrow('second_surname') }}</span></a>
                            </th>
                            <th class="hidden lg:table-cell">
                                <a href="{{ $sortUrl('birthday') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Birthday') }} <span>{{ $sortArrow('birthday') }}</span></a>
                            </th>
                            <th class="hidden xl:table-cell">{{ __('Parents') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($people as $person)
                            @php
                                $genderSymbol = $person->genderSymbol();
                                $genderColor = match ($genderSymbol) {
                                    '♂' => 'text-sky-500 dark:text-sky-400',
                                    '♀' => 'text-rose-500 dark:text-rose-400',
                                    default => 'text-violet-500 dark:text-violet-400',
                                };
                                $zodiacSymbol = $person->zodiacSymbol();
                            @endphp
                            <tr>
                                <td class="hidden sm:table-cell">
                                    <img src="{{ $person->picture_url }}" alt="" loading="lazy" class="h-9 w-9 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)]">
                                </td>
                                <td class="hidden text-xs text-gray-500 xl:table-cell">{{ $person->id }}</td>
                                <td>
                                    <p class="flex items-center gap-2 font-medium">
                                        <img src="{{ $person->picture_url }}" alt="" loading="lazy" class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)] sm:hidden">
                                        <span class="inline-flex items-center gap-1 text-sm leading-none" aria-hidden="true">
                                            <span class="{{ $genderColor }}" title="{{ optional($person->gender)->name ?: __('Gender') }}">{{ $genderSymbol }}</span>
                                            @if ($zodiacSymbol)
                                                <span class="text-amber-500 dark:text-amber-400" title="{{ $person->zodiacName() }}">{{ $zodiacSymbol }}</span>
                                            @endif
                                        </span>
                                        <span>{{ $person->name }}</span>
                                    </p>
                                </td>
                                <td class="hidden sm:table-cell">{{ $person->surname }}</td>
                                <td class="hidden text-xs xl:table-cell">{{ $person->second_surname ?: '-' }}</td>
                                <td class="hidden text-xs lg:table-cell">{{ optional($person->birthday)->format('Y-m-d') ?: '-' }}</td>
                                <td class="hidden text-xs xl:table-cell">
                                    {{ __('Father') }}: {{ optional($person->father)->name ? $person->father->name.' '.$person->father->surname : '-' }}<br>
                                    {{ __('Mother') }}: {{ optional($person->mother)->name ? $person->mother->name.' '.$person->mother->surname : '-' }}
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.people.show', $person) }}" title="{{ __('View') }}" aria-label="{{ __('View') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.people.edit', $person) }}" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 0 1 2.97 2.97L7.5 18.79 3 20l1.21-4.5L16.862 3.487Z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.people.graph', $person) }}" title="{{ __('Graph') }}" aria-label="{{ __('Graph') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="5" cy="6" r="2"/>
                                                <circle cx="19" cy="6" r="2"/>
                                                <circle cx="12" cy="18" r="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7.5 10.5 16m6-8.5L13 16"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.people.destroy', $person) }}" data-confirm="{{ __('Delete this person?') }}">
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
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No people found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $people->links() }}
            </div>
        </section>
    </div>

@endsection
