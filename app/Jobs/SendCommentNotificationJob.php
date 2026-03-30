<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCommentNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $commentId)
    {
    }

    public function handle(): void
    {
        $comment = Comment::query()->with('post')->find($this->commentId);

        if (! $comment) {
            return;
        }

        // Placeholder notification path until a dedicated mail/notification channel is introduced.
        Log::info('Pending comment submitted.', [
            'comment_id' => $comment->id,
            'post_id' => $comment->post_id,
            'post_slug' => $comment->post?->slug,
        ]);
    }
}
