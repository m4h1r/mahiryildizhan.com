@extends('admin.layout', ['title' => 'Timeline', 'heading' => 'Timeline'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin md:col-span-2" name="q" placeholder="Search timeline events..." value="{{ $filters['q'] ?? '' }}">
                <select name="event_type" class="form-input-admin">
                    <option value="">All Types</option>
                    @foreach (['milestone', 'process'] as $type)
                        <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select name="is_public" class="form-input-admin">
                    <option value="">All Visibility</option>
                    <option value="1" @selected(($filters['is_public'] ?? '') === '1')>Public</option>
                    <option value="0" @selected(($filters['is_public'] ?? '') === '0')>Private</option>
                </select>
                <div class="flex gap-2 md:col-span-4">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.timeline.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Timeline Events</h2>
                <a href="{{ route('admin.timeline.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Event</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Title</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Start</th>
                            <th class="px-4 py-3 text-left font-medium">Public</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($events as $event)
                            <tr>
                                <td class="px-4 py-3">{{ $event->title }}</td>
                                <td class="px-4 py-3">{{ ucfirst($event->event_type) }}</td>
                                <td class="px-4 py-3">{{ optional($event->start_date)->toDateString() }}</td>
                                <td class="px-4 py-3">{{ $event->is_public ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.timeline.edit', $event) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.timeline.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No timeline events found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $events->links() }}</div>
        </section>
    </div>
@endsection