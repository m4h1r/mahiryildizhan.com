@extends('admin.layout', ['title' => 'Subscribers', 'heading' => 'Subscribers'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2 lg:col-span-1" name="q" placeholder="{{ __('Search subscribers...') }}" value="{{ $filters['q'] ?? '' }}">
                <select name="status" class="form-input-admin">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('Pending') }}</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('Active') }}</option>
                    <option value="unsubscribed" @selected(($filters['status'] ?? '') === 'unsubscribed')>{{ __('Unsubscribed') }}</option>
                </select>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.subscribers.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Subscriber List') }}</h2>
                <a href="{{ route('admin.subscribers.export') }}" class="admin-btn admin-btn-ghost">{{ __('Export CSV') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="hidden md:table-cell">{{ __('Subscribed At') }}</th>
                            <th class="hidden lg:table-cell">{{ __('Mailchimp ID') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscribers as $subscriber)
                            <tr>
                                <td>{{ $subscriber->email }}</td>
                                <td>
                                    @if ($subscriber->status === 'active')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('Active') }}</span>
                                    @elseif ($subscriber->status === 'pending')
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Pending') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ __('Unsubscribed') }}</span>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell">{{ optional($subscriber->subscribed_at)->toDateTimeString() }}</td>
                                <td class="hidden lg:table-cell">{{ $subscriber->mailchimp_id ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        @if ($subscriber->status === 'active')
                                            <form method="POST" action="{{ route('admin.subscribers.unsubscribe', $subscriber) }}">
                                                @csrf
                                                <button type="submit" class="admin-btn-sm admin-btn-ghost">{{ __('Unsubscribe') }}</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber) }}" data-confirm="{{ __('Delete this subscriber?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No subscribers found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">{{ $subscribers->links() }}</div>
        </section>
    </div>
@endsection
