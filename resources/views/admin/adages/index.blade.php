@extends('admin.layout', ['title' => 'Adages', 'heading' => 'Adages'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="flex flex-wrap gap-3">
                <input class="form-input-admin" name="q" placeholder="{{ __('Search adages...') }}" value="{{ $filters['q'] ?? '' }}">
                <select name="language_id" class="form-input-admin">
                    <option value="">{{ __('All languages') }}</option>
                    @foreach ($languages as $lang)
                        <option value="{{ $lang->id }}" @selected((string) ($filters['language_id'] ?? '') === (string) $lang->id)>
                            {{ $lang->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-primary shrink-0">{{ __('Filter') }}</button>
                <a href="{{ route('admin.adages.index') }}" class="admin-btn admin-btn-ghost shrink-0">{{ __('Reset') }}</a>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Adage List') }}</h2>
                <a href="{{ route('admin.adages.create') }}" class="admin-btn admin-btn-primary">{{ __('New Adage') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell">{{ __('Owner') }}</th>
                            <th>{{ __('Adage') }}</th>
                            <th class="hidden md:table-cell">{{ __('Language') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adages as $adage)
                            <tr>
                                <td class="hidden sm:table-cell">{{ $adage->owner }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($adage->adage, 110) }}</td>
                                <td class="hidden md:table-cell">{{ optional($adage->language)->name ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.adages.edit', $adage) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>
                                        <form method="POST" action="{{ route('admin.adages.destroy', $adage) }}" data-confirm="{{ __('Delete this adage?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No adages found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">{{ $adages->links() }}</div>
        </section>
    </div>
@endsection
