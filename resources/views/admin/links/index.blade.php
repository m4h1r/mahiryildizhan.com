@extends('admin.layout', ['title' => 'Links', 'heading' => 'Links'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="flex gap-3">
                <input class="form-input-admin" name="q" placeholder="Search links..." value="{{ $filters['q'] ?? '' }}">
                <button type="submit" class="admin-btn admin-btn-primary shrink-0">Filter</button>
                <a href="{{ route('admin.links.index') }}" class="admin-btn admin-btn-ghost shrink-0">Reset</a>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Link List</h2>
                <a href="{{ route('admin.links.create') }}" class="admin-btn admin-btn-primary">New Link</a>
            </div>
            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell">Slug</th>
                            <th>Name</th>
                            <th class="hidden md:table-cell">Downloads</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($links as $link)
                            <tr>
                                <td class="hidden sm:table-cell">{{ $link->slug }}</td>
                                <td>{{ $link->original_name }}</td>
                                <td class="hidden md:table-cell">{{ number_format($link->download_count) }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('links.show', $link->slug) }}" target="_blank" class="admin-btn-sm admin-btn-ghost">Open</a>
                                        <a href="{{ route('admin.links.edit', $link) }}" class="admin-btn-sm admin-btn-ghost">Edit</a>
                                        <form method="POST" action="{{ route('admin.links.destroy', $link) }}" onsubmit="return confirm('Delete this link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No links found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">{{ $links->links() }}</div>
        </section>
    </div>
@endsection
