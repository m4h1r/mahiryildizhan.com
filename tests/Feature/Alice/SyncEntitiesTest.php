<?php

namespace Tests\Feature\Alice;

class SyncEntitiesTest extends AliceTestCase
{
    /**
     * A minimal valid create payload per new entity.
     *
     * @return array<string, array<string, mixed>>
     */
    private function payloads(): array
    {
        return [
            'projects' => ['slug' => 'alice-panel', 'name' => 'Alice Panel', 'status' => 'aktif', 'progress' => 40],
            'agenda_events' => ['title' => 'Toplantı', 'starts_at' => '2026-12-01T10:00:00+03:00', 'all_day' => false],
            'wealth_entries' => ['kind' => 'cash', 'label' => 'Kasa', 'amount' => 1500.50, 'currency' => 'TRY', 'as_of' => '2026-11-01'],
            'reading_logs' => ['book_title' => 'Tutunamayanlar', 'author' => 'Oğuz Atay', 'pages' => 724, 'read_on' => '2026-10-20'],
        ];
    }

    public function test_each_entity_create_appears_in_snapshot_and_digest(): void
    {
        foreach ($this->payloads() as $entity => $payload) {
            $created = $this->alicePost($entity, $payload, $this->idempotencyKey())
                ->assertCreated()
                ->assertJsonPath('data.version', 1);

            $id = $created->json('data.id');

            // Snapshot lists it with the start cursor.
            $this->aliceGet('sync/snapshot', ['entity' => $entity])
                ->assertOk()
                ->assertJsonPath('meta.total', 1)
                ->assertJsonPath('data.0.id', $id);

            // Digest reports one non-deleted record.
            $this->aliceGet('sync/digest')
                ->assertOk()
                ->assertJsonPath("entities.{$entity}.count", 1)
                ->assertJsonPath("entities.{$entity}.max_version", 1);
        }
    }

    public function test_project_update_and_delete_follow_version_contract(): void
    {
        $created = $this->alicePost('projects', ['slug' => 'p1', 'name' => 'P1'], $this->idempotencyKey())->assertCreated();
        $id = $created->json('data.id');

        // Stale If-Match → 412.
        $this->alicePatch("projects/{$id}", ['progress' => 10], $this->ifMatch(99))->assertStatus(412);

        // Matching → 200 and version bump.
        $this->alicePatch("projects/{$id}", ['progress' => 10], $this->ifMatch(1))
            ->assertOk()
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.progress', 10);

        // Delete needs the current version.
        $this->aliceDelete("projects/{$id}", $this->ifMatch(2))->assertOk();
        $this->assertSoftDeleted('projects', ['id' => $id]);
    }

    public function test_project_slug_is_unique(): void
    {
        $this->alicePost('projects', ['slug' => 'dup', 'name' => 'A'], $this->idempotencyKey())->assertCreated();

        $this->alicePost('projects', ['slug' => 'dup', 'name' => 'B'], $this->idempotencyKey())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_agenda_source_ref_is_unique(): void
    {
        $payload = ['title' => 'E', 'starts_at' => '2026-12-01T10:00:00+03:00', 'source_ref' => 'gcal:abc'];
        $this->alicePost('agenda_events', $payload, $this->idempotencyKey())->assertCreated();

        $this->alicePost('agenda_events', $payload, $this->idempotencyKey())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_changes_feed_carries_new_entity_record(): void
    {
        $created = $this->alicePost('reading_logs', ['book_title' => 'Kürk Mantolu Madonna', 'pages' => 160, 'read_on' => '2026-09-01'], $this->idempotencyKey())
            ->assertCreated();
        $id = $created->json('data.id');

        $data = $this->aliceGet('sync/changes', ['after' => 0, 'limit' => 1000])->assertOk()->json('data');
        $row = collect($data)->firstWhere(fn ($c) => $c['entity'] === 'reading_logs' && $c['id'] === $id);

        $this->assertNotNull($row);
        $this->assertSame('upsert', $row['op']);
        $this->assertSame('Kürk Mantolu Madonna', $row['record']['book_title']);
    }
}
