<?php

namespace Tests\Feature\Alice;

use App\Models\Person;

class SyncWriteTest extends AliceTestCase
{
    public function test_post_requires_idempotency_key(): void
    {
        $this->alicePost('people', ['name' => 'No', 'surname' => 'Key'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'idempotency_key_required');
    }

    public function test_same_idempotency_key_replays_with_header(): void
    {
        $headers = $this->idempotencyKey('fixed-key');
        $payload = ['name' => 'Rep', 'surname' => 'Lay'];

        $first = $this->alicePost('people', $payload, $headers)->assertCreated();
        $second = $this->alicePost('people', $payload, $headers)
            ->assertCreated()
            ->assertHeader('Idempotent-Replay', 'true');

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('people', 1);
    }

    public function test_patch_requires_if_match(): void
    {
        $person = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);

        $this->alicePatch("people/{$person->id}", ['nickname' => 'x'])
            ->assertStatus(428)
            ->assertJsonPath('error', 'precondition_required');
    }

    public function test_patch_version_mismatch_returns_412_with_current(): void
    {
        $person = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);

        $response = $this->alicePatch("people/{$person->id}", ['nickname' => 'x'], $this->ifMatch(999))
            ->assertStatus(412)
            ->assertJsonPath('error', 'version_conflict')
            ->assertJsonPath('version', 1);

        $this->assertSame($person->id, $response->json('current.id'));
    }

    public function test_patch_with_matching_version_succeeds_and_bumps(): void
    {
        $person = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);

        $this->alicePatch("people/{$person->id}", ['nickname' => 'ok'], $this->ifMatch($person->version))
            ->assertOk()
            ->assertJsonPath('data.version', 2);
    }

    public function test_delete_requires_matching_version(): void
    {
        $person = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);

        $this->aliceDelete("people/{$person->id}", $this->ifMatch(5))->assertStatus(412);
        $this->aliceDelete("people/{$person->id}", $this->ifMatch($person->version))->assertOk();
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    public function test_assessment_duplicate_external_ref_returns_409(): void
    {
        $person = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        $payload = ['assessed_at' => '2026-11-03', 'external_ref' => 'alice-card:a:2026-11-03'];

        $first = $this->alicePost("people/{$person->id}/assessments", $payload, $this->idempotencyKey())
            ->assertCreated();

        $this->alicePost("people/{$person->id}/assessments", $payload, $this->idempotencyKey())
            ->assertStatus(409)
            ->assertJsonPath('error', 'external_ref_conflict')
            ->assertJsonPath('id', $first->json('data.id'));
    }

    public function test_rate_limit_returns_429_with_retry_after(): void
    {
        config(['alice.rate_limit' => 3]);

        $hit429 = false;
        for ($i = 0; $i < 6; $i++) {
            $response = $this->aliceGet('health');
            if ($response->getStatusCode() === 429) {
                $response->assertHeader('Retry-After');
                $hit429 = true;
                break;
            }
        }

        $this->assertTrue($hit429, 'Expected a 429 once the per-minute cap was exceeded.');
    }
}
