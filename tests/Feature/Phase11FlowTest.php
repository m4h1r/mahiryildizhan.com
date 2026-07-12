<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase11FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_timeline_shows_only_public_events(): void
    {
        TimelineEvent::query()->create([
            'title' => 'Public Event',
            'event_type' => 'milestone',
            'start_date' => now()->toDateString(),
            'is_public' => true,
        ]);

        TimelineEvent::query()->create([
            'title' => 'Private Event',
            'event_type' => 'process',
            'start_date' => now()->toDateString(),
            'is_public' => false,
        ]);

        $response = $this->get(route('timeline.public'));

        $response
            ->assertOk()
            ->assertSee('Public Event')
            ->assertDontSee('Private Event');
    }

    public function test_subscribe_works_without_mailchimp_credentials(): void
    {
        config([
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]);

        putenv('MAILCHIMP_API_KEY');
        putenv('MAILCHIMP_LIST_ID');
        putenv('MAILCHIMP_DATACENTER');

        $response = $this->from(route('home'))->post(route('public.subscribers.store'), [
            'email' => 'reader@example.com',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('subscribers', [
            'email' => 'reader@example.com',
            'status' => 'active',
            'mailchimp_id' => null,
        ]);
    }

    public function test_subscribe_stores_mailchimp_member_id_when_api_succeeds(): void
    {
        putenv('MAILCHIMP_API_KEY=test-key-us1');
        putenv('MAILCHIMP_LIST_ID=list123');
        putenv('MAILCHIMP_DATACENTER=us1');

        Http::fake([
            'https://us1.api.mailchimp.com/3.0/lists/list123/members' => Http::response([
                'id' => 'member_abc',
            ], 200),
        ]);

        $response = $this->from(route('home'))->post(route('public.subscribers.store'), [
            'email' => 'sync@example.com',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('subscribers', [
            'email' => 'sync@example.com',
            'mailchimp_id' => 'member_abc',
        ]);

        Http::assertSentCount(1);
    }

    public function test_admin_export_route_returns_csv_download(): void
    {
        Subscriber::query()->create([
            'email' => 'csv@example.com',
            'status' => 'active',
            'subscribed_at' => now(),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.subscribers.export'));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
