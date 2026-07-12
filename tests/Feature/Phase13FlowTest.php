<?php

namespace Tests\Feature;

use App\Models\Adage;
use App\Models\Link;
use App\Models\Person;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase13FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_file_link_redirects_and_increments_download_count(): void
    {
        $link = Link::query()->create([
            'slug' => 'my-file',
            'file_path' => 'https://example.com/file.pdf',
            'original_name' => 'My File',
        ]);

        $response = $this->get(route('links.show', $link->slug));

        $response->assertRedirect('https://example.com/file.pdf');

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'download_count' => 1,
        ]);
    }

    public function test_biolink_page_lists_links(): void
    {
        Link::query()->create([
            'slug' => 'resource-1',
            'file_path' => 'https://example.com/resource-1',
            'original_name' => 'Resource One',
        ]);

        $response = $this->get(route('biolink'));

        $response
            ->assertOk()
            ->assertSee('Resource One')
            ->assertSee('/resource-1');
    }

    public function test_search_returns_posts_adages_and_people(): void
    {
        $author = User::factory()->create();

        Post::query()->create([
            'title' => 'Alpha Post',
            'slug' => 'alpha-post',
            'body' => 'Alpha content body',
            'user_id' => $author->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now(),
        ]);

        Adage::query()->create([
            'owner' => 'Alpha Owner',
            'adage' => 'Alpha wisdom quote',
        ]);

        Person::query()->create([
            'name' => 'Alpha',
            'surname' => 'Person',
        ]);

        $response = $this->get(route('search', ['q' => 'Alpha']));

        $response
            ->assertOk()
            ->assertSee('Alpha Post')
            ->assertSee('Alpha Owner')
            ->assertSee('Alpha Person');
    }
}
