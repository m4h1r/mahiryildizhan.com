@extends('admin.layout', ['title' => 'Comments', 'heading' => 'Comments'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2" name="q" placeholder="Search body, guest name or email..." value="{{ $filters['q'] ?? '' }}">

                <select name="approval" class="form-input-admin">
                    <option value="">All Statuses</option>
                    <option value="approved" @selected(($filters['approval'] ?? '') === 'approved')>Approved</option>
                    <option value="pending" @selected(($filters['approval'] ?? '') === 'pending')>Pending</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">Filter</button>
                    <a href="{{ route('admin.comments.index') }}" class="admin-btn admin-btn-ghost flex-1">Reset</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th class="hidden sm:table-cell">Author</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th class="hidden lg:table-cell">Spam Score</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comments as $comment)
                            <tr class="align-top">
                                <td>
                                    <p class="font-medium">{{ optional($comment->post)->title ?: 'Deleted post' }}</p>
                                    @if ($comment->post)
                                        <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-xs text-[var(--color-brand)] hover:underline">View post</a>
                                    @endif
                                </td>
                                <td class="hidden sm:table-cell">
                                    {{ $comment->guest_name ?: optional($comment->user)->name ?: '-' }}<br>
                                    <span class="text-xs text-gray-500">{{ $comment->guest_email ?: optional($comment->user)->email ?: '-' }}</span>
                                </td>
                                <td>
                                    <p class="line-clamp-4 max-w-xs leading-relaxed lg:max-w-xl">{{ $comment->body }}</p>
                                </td>
                                <td>
                                    @if ($comment->is_approved)
                                        <span class="inline-flex items-center rounded-full border border-emerald-300 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:border-emerald-900 dark:text-emerald-300">Approved</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-amber-300 px-2 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-900 dark:text-amber-300">Pending</span>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell">{{ $comment->spam_score !== null ? number_format((float) $comment->spam_score, 3) : '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        @unless($comment->is_approved)
                                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="admin-btn-sm admin-btn-ghost">Approve</button>
                                            </form>
                                        @endunless

                                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
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

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $comments->links() }}
            </div>
        </section>
    </div>
@endsection
