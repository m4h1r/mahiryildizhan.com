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
        <section class="card-admin">
            <form id="people-filter-form" method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <input
                    id="people-live-search"
                    type="search"
                    name="q"
                    class="form-input-admin sm:col-span-2 lg:col-span-1 xl:col-span-2"
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

                <select name="scope" class="form-input-admin">
                    <option value="">{{ __('All relationships') }}</option>
                    @foreach (config('crm.relationship_scopes') as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['scope'] ?? '') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>

                <label class="glass-well flex min-h-11 items-center gap-2 px-3 text-sm text-ink-soft">
                    <input type="checkbox" name="missing_assessment" value="1" class="h-4 w-4 accent-[var(--color-brand)]" @checked(! empty($filters['missing_assessment']))>
                    {{ __('Only people without analysis') }}
                </label>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.people.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <div x-data="{ view: (() => { try { return localStorage.getItem('people_view') || 'cards'; } catch (e) { return 'cards'; } })(), set(v) { this.view = v; try { localStorage.setItem('people_view', v); } catch (e) {} } }" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-ink-muted">{{ trans_choice(':count person|:count people', $people->total(), ['count' => $people->total()]) }}</p>
            <div class="flex items-center gap-2">
                <div class="glass-well inline-flex gap-1 p-1" role="group" aria-label="{{ __('View') }}">
                    <button type="button" @click="set('cards')" :aria-pressed="(view === 'cards').toString()" class="rounded-[10px] px-3 py-1.5 text-sm font-medium" :class="view === 'cards' ? 'bg-[var(--glass-strong)] text-ink shadow-sm' : 'text-ink-muted'">{{ __('Cards') }}</button>
                    <button type="button" @click="set('table')" :aria-pressed="(view === 'table').toString()" class="rounded-[10px] px-3 py-1.5 text-sm font-medium" :class="view === 'table' ? 'bg-[var(--glass-strong)] text-ink shadow-sm' : 'text-ink-muted'">{{ __('Table') }}</button>
                </div>
                <a href="{{ route('admin.people.create') }}" class="admin-btn admin-btn-primary">{{ __('New Person') }}</a>
            </div>
        </div>

        {{-- Card grid --}}
        <div x-show="view === 'cards'" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($people as $person)
                @php
                    $latest = $person->latestAssessment;
                @endphp
                <a href="{{ route('admin.people.show', $person) }}" class="card-admin group flex min-w-0 items-center gap-3 !p-3.5 transition-shadow hover:shadow-[var(--glass-shadow-lift)]">
                    <img src="{{ $person->avatarUrl() }}" alt="" loading="lazy" class="h-14 w-14 shrink-0 rounded-[18px] border border-[var(--glass-edge)] object-cover">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-ink group-hover:text-[var(--brand-ink)]">{{ $person->fullName() }}</span>
                        <span class="block truncate text-xs text-ink-muted">
                            {{ $person->relationship_label ?: ($person->scopeLabel() ?: ($person->birthLabel() ?: '—')) }}
                        </span>
                        <span class="mt-1.5 flex flex-wrap gap-1">
                            @if ($person->scopeLabel())
                                <span class="chip min-h-5 px-2 text-[10.5px]">{{ $person->scopeLabel() }}</span>
                            @endif
                            @if ($latest)
                                <span class="chip chip-brand min-h-5 px-2 text-[10.5px]">{{ __('Analysis') }} {{ $latest->overall_confidence !== null ? '%'.(int) round($latest->overall_confidence * 100) : '' }}</span>
                            @else
                                <span class="chip min-h-5 px-2 text-[10.5px] text-ink-muted">{{ __('No analysis') }}</span>
                            @endif
                        </span>
                    </span>
                </a>
            @empty
                <div class="card-admin text-sm text-ink-muted sm:col-span-2 xl:col-span-3">{{ __('No people found.') }}</div>
            @endforelse
        </div>

        <section class="admin-table-shell" x-show="view === 'table'" x-cloak>
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
                                    <img src="{{ $person->avatarUrl() }}" alt="" loading="lazy" class="h-9 w-9 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)]">
                                </td>
                                <td class="hidden text-xs text-gray-500 xl:table-cell">{{ $person->id }}</td>
                                <td>
                                    <p class="flex items-center gap-2 font-medium">
                                        <img src="{{ $person->avatarUrl() }}" alt="" loading="lazy" class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-[var(--color-admin-border)] dark:ring-[var(--color-admin-border-dark)] sm:hidden">
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
                                <td class="hidden text-xs lg:table-cell">{{ $person->birthLabel() ?: '—' }}</td>
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

        </section>

        <div class="px-1">
            {{ $people->links() }}
        </div>
        </div>
    </div>

@endsection
