@extends('admin.layout', ['title' => 'Subscribers', 'heading' => 'Subscribers'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin md:col-span-2" name="q" placeholder="Search subscribers..." value="{{ $filters['q'] ?? '' }}">
                <select name="status" class="form-input-admin">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="unsubscribed" @selected(($filters['status'] ?? '') === 'unsubscribed')>Unsubscribed</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.subscribers.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Subscriber List</h2>
                <a href="{{ route('admin.subscribers.export') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Export CSV</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Subscribed At</th>
                            <th class="px-4 py-3 text-left font-medium">Mailchimp ID</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($subscribers as $subscriber)
                            <tr>
                                <td class="px-4 py-3">{{ $subscriber->email }}</td>
                                <td class="px-4 py-3">{{ $subscriber->status }}</td>
                                <td class="px-4 py-3">{{ optional($subscriber->subscribed_at)->toDateTimeString() }}</td>
                                <td class="px-4 py-3">{{ $subscriber->mailchimp_id ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if ($subscriber->status === 'active')
                                            <form method="POST" action="{{ route('admin.subscribers.unsubscribe', $subscriber) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Unsubscribe</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber) }}" onsubmit="return confirm('Delete this subscriber?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No subscribers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $subscribers->links() }}</div>
        </section>
    </div>
@endsection