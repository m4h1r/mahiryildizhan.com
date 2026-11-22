<?php

namespace Tests\Feature\Alice;

use App\Models\Person;
use App\Models\SyncChange;

class SyncReadTest extends AliceTestCase
{
    public function test_head_reports_version_and_cursor(): void
    {
        Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        $maxId = (int) SyncChange::max('id');

        $this->aliceGet('sync/head')
            ->assertOk()
            ->assertJsonPath('api_version', '1.4.0')
            ->assertJsonPath('cursor', $maxId)
            ->assertJsonStructure(['api_version', 'cursor', 'server_time']);
    }

    public function test_health_reports_contract_version(): void
    {
        $this->aliceGet('health')->assertOk()->assertJsonPath('version', '1.4.0');
    }

    public function test_changes_feed_upsert_carries_record_delete_is_null(): void
    {
        $kept = Person::create(['name' => 'Kept', 'surname' => 'One', 'notes' => '']);
        $gone = Person::create(['name' => 'Gone', 'surname' => 'Two', 'notes' => '']);
        $gone->delete();

        $response = $this->aliceGet('sync/changes', ['after' => 0, 'limit' => 500])->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // Ordered ascending by change_id.
        $ids = array_column($data, 'change_id');
        $sorted = $ids;
        sort($sorted);
        $this->assertSame($sorted, $ids);

        // The delete row carries a null record; an upsert row carries the resource.
        $delete = collect($data)->firstWhere(fn ($c) => $c['op'] === 'delete' && $c['id'] === $gone->id);
        $this->assertNotNull($delete);
        $this->assertNull($delete['record']);

        $upsert = collect($data)->firstWhere(fn ($c) => $c['op'] === 'upsert' && $c['id'] === $kept->id);
        $this->assertNotNull($upsert);
        $this->assertSame($kept->id, $upsert['record']['id']);
        $this->assertSame(1, $upsert['record']['version']);
    }

    public function test_changes_paginates_with_has_more_and_next_cursor(): void
    {
        Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        Person::create(['name' => 'B', 'surname' => 'Two', 'notes' => '']);
        Person::create(['name' => 'C', 'surname' => 'Three', 'notes' => '']);

        $first = $this->aliceGet('sync/changes', ['after' => 0, 'limit' => 2])->assertOk();
        $first->assertJsonPath('has_more', true);
        $nextCursor = $first->json('next_cursor');

        $second = $this->aliceGet('sync/changes', ['after' => $nextCursor, 'limit' => 2])->assertOk();
        $second->assertJsonPath('has_more', false);
    }

    public function test_digest_hash_and_empty_entity(): void
    {
        Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        Person::create(['name' => 'B', 'surname' => 'Two', 'notes' => '']);

        $lines = Person::query()->get(['id', 'version'])
            ->map(fn ($r) => $r->id.':'.$r->version)->all();
        sort($lines, SORT_STRING);
        $expected = sha1(implode("\n", $lines));

        $response = $this->aliceGet('sync/digest')->assertOk();

        $response->assertJsonPath('entities.people.count', 2);
        $response->assertJsonPath('entities.people.max_version', 1);
        $response->assertJsonPath('entities.people.hash', $expected);

        // An entity with no rows hashes the empty string.
        $response->assertJsonPath('entities.assessments.count', 0);
        $response->assertJsonPath('entities.assessments.max_version', 0);
        $response->assertJsonPath('entities.assessments.hash', sha1(''));
    }

    public function test_snapshot_returns_records_and_cursor(): void
    {
        Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        $cursor = (int) SyncChange::max('id');

        $response = $this->aliceGet('sync/snapshot', ['entity' => 'people', 'per_page' => 200])->assertOk();

        $response->assertJsonPath('cursor', $cursor);
        $response->assertJsonPath('meta.total', 1);
        $this->assertSame('A', $response->json('data.0.name'));
    }

    public function test_snapshot_rejects_unknown_entity(): void
    {
        $this->aliceGet('sync/snapshot', ['entity' => 'dragons'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'unknown_entity');
    }
}
