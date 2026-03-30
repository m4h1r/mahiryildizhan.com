@extends('admin.layout', ['title' => 'Posts', 'heading' => 'Posts'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-5">
                <input class="form-input-admin md:col-span-2" name="q" placeholder="Search title, slug, excerpt..." value="{{ $filters['q'] ?? '' }}">

                <select name="status" class="form-input-admin">
                    <option value="">All Statuses</option>
                    @foreach (['draft', 'published', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <select name="category_id" class="form-input-admin">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="language_id" class="form-input-admin">
                    <option value="">All Languages</option>
                    @foreach ($languages as $language)
                        <option value="{{ $language->id }}" @selected((string) ($filters['language_id'] ?? '') === (string) $language->id)>{{ $language->name }}</option>
                    @endforeach
                </select>

                <select name="published" class="form-input-admin">
                    <option value="">All Publish States</option>
                    <option value="1" @selected(($filters['published'] ?? '') === '1')>Published</option>
                    <option value="0" @selected(($filters['published'] ?? '') === '0')>Unpublished</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.posts.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Post List</h2>
                <a href="{{ route('admin.posts.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Post</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Title</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Language</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Stats</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($posts as $post)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $post->title }}</p>
                                    <p class="mt-1 text-xs text-gray-500">/{{ $post->slug }}</p>
                                </td>
                                <td class="px-4 py-3">{{ optional($post->category)->name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ optional($post->language)->code ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">{{ ucfirst($post->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    {{ number_format((int) $post->word_count) }} words / {{ (int) $post->reading_time }} min<br>
                                    {{ number_format((int) $post->view_count) }} views ({{ number_format((int) $post->unique_view_count) }} unique)
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>

                                        @if ($post->published)
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">View</a>
                                        @endif

                                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No posts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $posts->links() }}
            </div>
        </section>
    </div>
@endsection
