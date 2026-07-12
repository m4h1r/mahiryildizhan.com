<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Jobs\SendCommentNotificationJob;
use App\Models\Comment;
use App\Models\Post;
use App\Services\RecaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

class CommentController extends Controller
{
    public function __construct(private readonly RecaptchaService $recaptchaService) {}

    public function store(StoreCommentRequest $request): RedirectResponse|Response
    {
        $payload = $request->validated();
        $post = Post::query()
            ->where('id', $payload['post_id'])
            ->where('published', true)
            ->where('status', 'published')
            ->firstOrFail();

        if (! $request->user() && (empty($payload['guest_name']) || empty($payload['guest_email']))) {
            return back()->withErrors([
                'comment' => __('Name and email are required for guest comments.'),
            ])->withInput();
        }

        if (! empty($payload['website'])) {
            return response('', 200);
        }

        $rateKey = sprintf('comment:%s:%s', $post->id, $request->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            return back()->withErrors([
                'comment' => __('Please wait before submitting another comment.'),
            ])->withInput();
        }

        RateLimiter::hit($rateKey, 120);

        $recaptchaResult = $this->recaptchaService->verify(
            $payload['recaptcha_token'] ?? null,
            $request->ip()
        );

        if (! $recaptchaResult['success']) {
            return back()->withErrors(['comment' => __('reCAPTCHA verification failed.')])->withInput();
        }

        $score = $recaptchaResult['score'];

        if ($score !== null && $score < 0.5) {
            return back()->withErrors(['comment' => __('reCAPTCHA verification failed.')])->withInput();
        }

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $request->user()?->id,
            'parent_id' => $payload['parent_id'] ?? null,
            'guest_name' => $payload['guest_name'] ?? ($request->user()?->name),
            'guest_email' => $payload['guest_email'] ?? ($request->user()?->email),
            'body' => $payload['body'],
            'is_approved' => false,
            'spam_score' => $score,
            'recaptcha_score' => $score,
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ]);

        SendCommentNotificationJob::dispatch($comment->id);

        return to_route('blog.show', $post->slug)->with('success', __('Your comment has been submitted for review.'));
    }
}
