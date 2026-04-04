@extends('admin.layout', ['title' => 'Nodes', 'heading' => 'Nodes'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="flex gap-3">
                <input class="form-input-admin" name="q" placeholder="Search nodes..." value="{{ $filters['q'] ?? '' }}">
                <button type="submit" class="admin-btn admin-btn-primary shrink-0">Filter</button>
                <a href="{{ route('admin.nodes.index') }}" class="admin-btn admin-btn-ghost shrink-0">Reset</a>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Node List</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.nodes.graph') }}" class="admin-btn admin-btn-ghost">Graph View</a>
                    <a href="{{ route('admin.nodes.create') }}" class="admin-btn admin-btn-primary">New Node</a>
                </div>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="hidden sm:table-cell">Text</th>
                            <th class="hidden md:table-cell">Links</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nodes as $node)
                            <tr>
                                <td class="font-medium">{{ $node->name }}</td>
                                <td class="hidden text-xs text-gray-500 sm:table-cell">{{ $node->text_color ?: '-' }} / {{ $node->text_size ?: '-' }}</td>
                                <td class="hidden text-xs md:table-cell">{{ $node->links_from_count }} out / {{ $node->links_to_count }} in</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.nodes.edit', $node) }}" class="admin-btn-sm admin-btn-ghost">Edit</a>
                                        <form method="POST" action="{{ route('admin.nodes.destroy', $node) }}" onsubmit="return confirm('Delete this node?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No nodes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $nodes->links() }}
            </div>
        </section>
    </div>
@endsection
