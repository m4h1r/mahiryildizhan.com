@extends('admin.layout', ['title' => 'Stakeholders', 'heading' => 'Stakeholders'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2 lg:col-span-1" name="q" placeholder="{{ __('Search title, vkn, email...') }}" value="{{ $filters['q'] ?? '' }}">

                <select name="status" class="form-input-admin">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (['Active', 'Passive'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>

                <select name="company_type" class="form-input-admin">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach (['Company', 'Individual'] as $type)
                        <option value="{{ $type }}" @selected(($filters['company_type'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.stakeholders.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Stakeholder List') }}</h2>
                <a href="{{ route('admin.stakeholders.create') }}" class="admin-btn admin-btn-primary">{{ __('New Stakeholder') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell">VKN/TCKN</th>
                            <th>{{ __('Title') }}</th>
                            <th class="hidden md:table-cell">{{ __('Type') }}</th>
                            <th class="hidden md:table-cell">{{ __('Status') }}</th>
                            <th class="hidden lg:table-cell">{{ __('Contact') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stakeholders as $stakeholder)
                            <tr>
                                <td class="hidden sm:table-cell">{{ $stakeholder->vkn_tckn }}</td>
                                <td class="font-medium">{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</td>
                                <td class="hidden md:table-cell">{{ $stakeholder->company_type }}</td>
                                <td class="hidden md:table-cell">{{ $stakeholder->status }}</td>
                                <td class="hidden lg:table-cell">{{ $stakeholder->email ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.stakeholders.edit', $stakeholder) }}" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 0 1 2.97 2.97L7.5 18.79 3 20l1.21-4.5L16.862 3.487Z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.stakeholders.duplicate', $stakeholder) }}">
                                            @csrf
                                            <button type="submit" title="{{ __('Duplicate') }}" aria-label="{{ __('Duplicate') }}" class="admin-btn-sm admin-btn-ghost w-8 p-0">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="9" y="9" width="12" height="12" rx="2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.stakeholders.destroy', $stakeholder) }}" data-confirm="{{ __('Delete this stakeholder?') }}">
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
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No stakeholders found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $stakeholders->links() }}
            </div>
        </section>
    </div>
@endsection
