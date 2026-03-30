@extends('admin.layout', ['title' => 'Links', 'heading' => 'Links'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="flex gap-3">
                <input class="form-input-admin" name="q" placeholder="Search links..." value="{{ $filters['q'] ?? '' }}">
                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                <a href="{{ route('admin.links.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Link List</h2>
                <a href="{{ route('admin.links.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Link</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Slug</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Downloads</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($links as $link)
                            <tr>
                                <td class="px-4 py-3">{{ $link->slug }}</td>
                                <td class="px-4 py-3">{{ $link->original_name }}</td>
                                <td class="px-4 py-3">{{ number_format($link->download_count) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('links.show', $link->slug) }}" target="_blank" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Open</a>
                                        <a href="{{ route('admin.links.edit', $link) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.links.destroy', $link) }}" onsubmit="return confirm('Delete this link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
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
            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $links->links() }}</div>
        </section>
    </div>
@endsection