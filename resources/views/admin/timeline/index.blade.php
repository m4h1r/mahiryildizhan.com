@extends('admin.layout', ['title' => 'Timeline', 'heading' => 'Timeline'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2" name="q" placeholder="Search timeline events..." value="{{ $filters['q'] ?? '' }}">
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
                <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.timeline.index') }}" class="admin-btn admin-btn-ghost">Reset</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Timeline Events</h2>
                <a href="{{ route('admin.timeline.create') }}" class="admin-btn admin-btn-primary">New Event</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th class="hidden sm:table-cell">Type</th>
                            <th class="hidden md:table-cell">Start</th>
                            <th class="hidden lg:table-cell">Public</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $event->title }}</td>
                                <td class="hidden sm:table-cell">{{ ucfirst($event->event_type) }}</td>
                                <td class="hidden md:table-cell">{{ optional($event->start_date)->toDateString() }}</td>
                                <td class="hidden lg:table-cell">{{ $event->is_public ? 'Yes' : 'No' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.timeline.edit', $event) }}" class="admin-btn-sm admin-btn-ghost">Edit</a>
                                        <form method="POST" action="{{ route('admin.timeline.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
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

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">{{ $events->links() }}</div>
        </section>
    </div>
@endsection
