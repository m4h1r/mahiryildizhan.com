@extends('admin.layout', ['title' => 'Comments', 'heading' => 'Comments'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin md:col-span-2" name="q" placeholder="Search body, guest name or email..." value="{{ $filters['q'] ?? '' }}">

                <select name="approval" class="form-input-admin">
                    <option value="">All Statuses</option>
                    <option value="approved" @selected(($filters['approval'] ?? '') === 'approved')>Approved</option>
                    <option value="pending" @selected(($filters['approval'] ?? '') === 'pending')>Pending</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.comments.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Post</th>
                            <th class="px-4 py-3 text-left font-medium">Author</th>
                            <th class="px-4 py-3 text-left font-medium">Comment</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Spam Score</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($comments as $comment)
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ optional($comment->post)->title ?: 'Deleted post' }}</p>
                                    @if ($comment->post)
                                        <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-xs text-[var(--color-brand)] hover:underline">View post</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $comment->guest_name ?: optional($comment->user)->name ?: '-' }}<br>
                                    <span class="text-xs text-gray-500">{{ $comment->guest_email ?: optional($comment->user)->email ?: '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="line-clamp-4 max-w-xl leading-relaxed">{{ $comment->body }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($comment->is_approved)
                                        <span class="rounded-full border border-emerald-300 px-2 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-900 dark:text-emerald-300">Approved</span>
                                    @else
                                        <span class="rounded-full border border-amber-300 px-2 py-1 text-xs font-medium text-amber-700 dark:border-amber-900 dark:text-amber-300">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $comment->spam_score !== null ? number_format((float) $comment->spam_score, 3) : '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @unless($comment->is_approved)
                                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Approve</button>
                                            </form>
                                        @endunless

                                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No comments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $comments->links() }}
            </div>
        </section>
    </div>
@endsection
