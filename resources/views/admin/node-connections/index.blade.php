@extends('admin.layout', ['title' => 'Node Connections', 'heading' => 'Node Connections'])

@section('content')
    <section class="card-admin overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h2 class="text-sm font-semibold">Connection List</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.nodes.graph') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Graph View</a>
                <a href="{{ route('admin.node-connections.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Connection</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">From</th>
                        <th class="px-4 py-3 text-left font-medium">To</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($connections as $connection)
                        <tr>
                            <td class="px-4 py-3">{{ optional($connection->fromNode)->name ?: '-' }}</td>
                            <td class="px-4 py-3">{{ optional($connection->toNode)->name ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.node-connections.edit', $connection) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>
                                    <form method="POST" action="{{ route('admin.node-connections.destroy', $connection) }}" onsubmit="return confirm('Delete this connection?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No connections found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
            {{ $connections->links() }}
        </div>
    </section>
@endsection