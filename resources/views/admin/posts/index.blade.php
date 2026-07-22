@extends('admin.layout', ['title' => 'Posts', 'heading' => 'Posts'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <input class="form-input-admin sm:col-span-2 lg:col-span-3" name="q" placeholder="{{ __('Search title, slug, excerpt...') }}" value="{{ $filters['q'] ?? '' }}">

                <select name="status" class="form-input-admin">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (['draft', 'published', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <select name="category_id" class="form-input-admin">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="language_id" class="form-input-admin">
                    <option value="">{{ __('All Languages') }}</option>
                    @foreach ($languages as $language)
                        <option value="{{ $language->id }}" @selected((string) ($filters['language_id'] ?? '') === (string) $language->id)>{{ $language->name }}</option>
                    @endforeach
                </select>

                <select name="published" class="form-input-admin">
                    <option value="">{{ __('All Publish States') }}</option>
                    <option value="1" @selected(($filters['published'] ?? '') === '1')>{{ __('Published') }}</option>
                    <option value="0" @selected(($filters['published'] ?? '') === '0')>{{ __('Unpublished') }}</option>
                </select>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.posts.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Post List') }}</h2>
                <a href="{{ route('admin.posts.create') }}" class="admin-btn admin-btn-primary">{{ __('New Post') }}</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th class="hidden md:table-cell">{{ __('Category') }}</th>
                            <th class="hidden md:table-cell">{{ __('Language') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="hidden lg:table-cell">{{ __('Stats') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <p class="max-w-[220px] truncate font-medium">{{ $post->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500">/{{ $post->slug }}</p>
                                </td>
                                <td class="hidden md:table-cell">{{ optional($post->category)->name ?: '-' }}</td>
                                <td class="hidden md:table-cell">{{ optional($post->language)->code ?: '-' }}</td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border border-gray-200 px-2 py-0.5 text-xs font-medium dark:border-gray-700">{{ ucfirst($post->status) }}</span>
                                </td>
                                <td class="hidden text-xs text-gray-600 dark:text-gray-300 lg:table-cell">
                                    {{ number_format((int) $post->word_count) }} {{ __('words') }} / {{ (int) $post->reading_time }} {{ __('min') }}<br>
                                    {{ number_format((int) $post->view_count) }} {{ __('views') }} ({{ number_format((int) $post->unique_view_count) }} {{ __('unique') }})
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>

                                        @if ($post->published)
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="admin-btn-sm admin-btn-ghost">{{ __('View') }}</a>
                                        @endif

                                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="{{ __('Delete this post?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No posts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $posts->links() }}
            </div>
        </section>
    </div>
@endsection
