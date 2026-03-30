@extends('admin.layout', ['title' => 'Adages', 'heading' => 'Adages'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="flex gap-3">
                <input class="form-input-admin" name="q" placeholder="Search adages..." value="{{ $filters['q'] ?? '' }}">
                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                <a href="{{ route('admin.adages.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Adage List</h2>
                <a href="{{ route('admin.adages.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Adage</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Owner</th>
                            <th class="px-4 py-3 text-left font-medium">Adage</th>
                            <th class="px-4 py-3 text-left font-medium">Language</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($adages as $adage)
                            <tr>
                                <td class="px-4 py-3">{{ $adage->owner }}</td>
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($adage->adage, 110) }}</td>
                                <td class="px-4 py-3">{{ $adage->language ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.adages.edit', $adage) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.adages.destroy', $adage) }}" onsubmit="return confirm('Delete this adage?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No adages found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $adages->links() }}</div>
        </section>
    </div>
@endsection