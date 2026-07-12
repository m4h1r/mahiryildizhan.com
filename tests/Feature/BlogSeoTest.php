<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_published_posts(): void
    {
        $post = $this->createPublishedPost();

        $response = $this->get(route('sitemap.xml'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('blog.show', $post->slug), false);
    }

    public function test_blog_show_renders_json_ld_and_canonical_meta(): void
    {
        $post = $this->createPublishedPost([
            'title' => 'SEO Ready Post',
            'slug' => 'seo-ready-post',
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response
            ->assertOk()
            ->assertSee('<script type="application/ld+json"', false)
            ->assertSee('<link rel="canonical" href="'.route('blog.show', $post->slug).'">', false)
            ->assertSee('<meta property="og:title" content="SEO Title">', false);
    }

    private function createPublishedPost(array $attributes = []): Post
    {
        $user = User::factory()->create();

        return Post::query()->create(array_merge([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'excerpt' => 'Published excerpt',
            'body' => 'Published body content for SEO checks.',
            'user_id' => $user->id,
            'schema_type' => 'Article',
            'reading_time' => 1,
            'word_count' => 6,
            'status' => 'published',
            'published' => true,
            'published_at' => now(),
        ], $attributes));
    }
}
