<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CommentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('comment:1:127.0.0.1');

        parent::tearDown();
    }

    public function test_honeypot_submission_returns_silent_success(): void
    {
        $post = $this->createPublishedPost();

        $response = $this->post(route('public.comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Bot',
            'guest_email' => 'bot@example.com',
            'body' => 'Spam comment',
            'website' => 'https://spam.test',
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_low_recaptcha_score_is_rejected(): void
    {
        config(['services.recaptcha.secret' => 'test-secret']);
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.3,
            ]),
        ]);

        $post = $this->createPublishedPost();

        $response = $this->from(route('blog.show', $post->slug))->post(route('public.comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Visitor',
            'guest_email' => 'visitor@example.com',
            'body' => 'Needs approval',
            'recaptcha_token' => 'token',
        ]);

        $response
            ->assertRedirect(route('blog.show', $post->slug))
            ->assertSessionHasErrors(['comment']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_valid_comment_is_saved_as_pending_with_score(): void
    {
        config(['services.recaptcha.secret' => 'test-secret']);
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
            ]),
        ]);

        $post = $this->createPublishedPost();

        $response = $this->from(route('blog.show', $post->slug))->post(route('public.comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Reader',
            'guest_email' => 'reader@example.com',
            'body' => 'Thoughtful comment',
            'recaptcha_token' => 'token',
        ]);

        $response
            ->assertRedirect(route('blog.show', $post->slug))
            ->assertSessionHas('success', __('Your comment has been submitted for review.'));

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'guest_name' => 'Reader',
            'guest_email' => 'reader@example.com',
            'is_approved' => false,
            'spam_score' => 0.9,
            'recaptcha_score' => 0.9,
        ]);
    }

    public function test_comment_submission_is_rate_limited_for_two_minutes(): void
    {
        config(['services.recaptcha.secret' => null]);

        $post = $this->createPublishedPost(['id' => 1]);

        $payload = [
            'post_id' => $post->id,
            'guest_name' => 'Reader',
            'guest_email' => 'reader@example.com',
            'body' => 'First comment',
        ];

        $firstResponse = $this->from(route('blog.show', $post->slug))->post(route('public.comments.store'), $payload);
        $secondResponse = $this->from(route('blog.show', $post->slug))->post(route('public.comments.store'), $payload);

        $firstResponse->assertRedirect(route('blog.show', $post->slug));
        $secondResponse
            ->assertRedirect(route('blog.show', $post->slug))
            ->assertSessionHasErrors(['comment']);

        $this->assertDatabaseCount('comments', 1);
    }

    private function createPublishedPost(array $attributes = []): Post
    {
        $user = User::factory()->create();

        return Post::query()->create(array_merge([
            'title' => 'Comment Post',
            'slug' => 'comment-post',
            'excerpt' => 'Post excerpt',
            'body' => 'Published body content for comment flow testing.',
            'user_id' => $user->id,
            'schema_type' => 'Article',
            'reading_time' => 1,
            'word_count' => 7,
            'status' => 'published',
            'published' => true,
            'published_at' => now(),
        ], $attributes));
    }
}