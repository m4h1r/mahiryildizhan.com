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
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.timeline.visualize') }}" class="admin-btn admin-btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        Görüntüle
                    </a>
                    <a href="{{ route('admin.timeline.create') }}" class="admin-btn admin-btn-primary">New Event</a>
                </div>
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
