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
            <form id="people-filter-form" method="GET" class="mt-3 grid gap-3 md:grid-cols-4">
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

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.people.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">{{ __('People List') }}</h2>
                <a href="{{ route('admin.people.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">{{ __('New Person') }}</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="hidden px-4 py-3 text-left font-medium xl:table-cell">
                                <a href="{{ $sortUrl('id') }}" class="inline-flex items-center gap-1 hover:underline">ID <span>{{ $sortArrow('id') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Person') }} <span>{{ $sortArrow('name') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                <a href="{{ $sortUrl('surname') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Surname') }} <span>{{ $sortArrow('surname') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                <a href="{{ $sortUrl('second_surname') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Maiden Surname') }} <span>{{ $sortArrow('second_surname') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Gender') }}</th>
                            <th class="px-4 py-3 text-left font-medium">
                                <a href="{{ $sortUrl('birthday') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Birthday') }} <span>{{ $sortArrow('birthday') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                <a href="{{ $sortUrl('deathday') }}" class="inline-flex items-center gap-1 hover:underline">{{ __('Deathday') }} <span>{{ $sortArrow('deathday') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Parents') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Zodiac') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($people as $person)
                            <tr>
                                <td class="hidden px-4 py-3 text-xs text-gray-500 xl:table-cell">{{ $person->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $person->name }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $person->surname }}</td>
                                <td class="px-4 py-3 text-xs">{{ $person->second_surname ?: '-' }}</td>
                                <td class="px-4 py-3">{{ optional($person->gender)->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ optional($person->birthday)->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ optional($person->deathday)->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ __('Father') }}: {{ optional($person->father)->name ? $person->father->name.' '.$person->father->surname : '-' }}<br>
                                    {{ __('Mother') }}: {{ optional($person->mother)->name ? $person->mother->name.' '.$person->mother->surname : '-' }}
                                </td>
                                <td class="px-4 py-3">{{ $person->zodiacName() ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.people.show', $person) }}" title="{{ __('View') }}" aria-label="{{ __('View') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.people.edit', $person) }}" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 0 1 2.97 2.97L7.5 18.79 3 20l1.21-4.5L16.862 3.487Z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.people.graph', $person) }}" title="{{ __('Graph') }}" aria-label="{{ __('Graph') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="5" cy="6" r="2"/>
                                                <circle cx="19" cy="6" r="2"/>
                                                <circle cx="12" cy="18" r="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7.5 10.5 16m6-8.5L13 16"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.people.destroy', $person) }}" onsubmit="return confirm('{{ __('Delete this person?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 text-red-700 dark:border-red-900 dark:text-red-300">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-8 0v14m8-14v14M5 6l1 14h12l1-14"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No people found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $people->links() }}
            </div>
        </section>
    </div>

@endsection
