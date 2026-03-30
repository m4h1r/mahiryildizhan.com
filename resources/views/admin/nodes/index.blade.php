@extends('admin.layout', ['title' => 'Nodes', 'heading' => 'Nodes'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="flex gap-3">
                <input class="form-input-admin" name="q" placeholder="Search nodes..." value="{{ $filters['q'] ?? '' }}">
                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                <a href="{{ route('admin.nodes.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Node List</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.nodes.graph') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Graph View</a>
                    <a href="{{ route('admin.nodes.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Node</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Text</th>
                            <th class="px-4 py-3 text-left font-medium">Links</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($nodes as $node)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $node->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $node->text_color ?: '-' }} / {{ $node->text_size ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $node->links_from_count }} out / {{ $node->links_to_count }} in</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.nodes.edit', $node) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.nodes.destroy', $node) }}" onsubmit="return confirm('Delete this node?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
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

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $nodes->links() }}
            </div>
        </section>
    </div>
@endsection