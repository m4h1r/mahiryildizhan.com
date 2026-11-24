<?php

namespace Tests\Feature\Sync;

use App\Models\Person;
use App\Models\PersonAssessment;
use App\Models\SyncChange;
use App\Models\TodoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_logs_upsert_at_version_1(): void
    {
        $person = Person::create(['name' => 'Ada', 'surname' => 'Lovelace', 'notes' => '']);

        $this->assertSame(1, $person->version);
        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'people',
            'record_id' => (string) $person->id,
            'op' => 'upsert',
            'version' => 1,
        ]);
    }

    public function test_update_bumps_version_and_logs_upsert(): void
    {
        $person = Person::create(['name' => 'Ada', 'surname' => 'Lovelace', 'notes' => '']);
        SyncChange::query()->delete();

        $person->update(['nickname' => 'Countess']);

        $this->assertSame(2, $person->fresh()->version);
        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'people',
            'record_id' => (string) $person->id,
            'op' => 'upsert',
            'version' => 2,
        ]);
    }

    public function test_soft_delete_logs_delete(): void
    {
        $person = Person::create(['name' => 'Ada', 'surname' => 'Lovelace', 'notes' => '']);
        SyncChange::query()->delete();

        $person->delete();

        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'people',
            'record_id' => (string) $person->id,
            'op' => 'delete',
        ]);
    }

    public function test_restore_logs_upsert_and_bumps_version(): void
    {
        $person = Person::create(['name' => 'Ada', 'surname' => 'Lovelace', 'notes' => '']);
        $person->delete();
        SyncChange::query()->delete();

        $person->restore();

        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'people',
            'record_id' => (string) $person->id,
            'op' => 'upsert',
        ]);
        $this->assertGreaterThan(1, $person->fresh()->version);
    }

    public function test_todo_and_assessment_entity_names(): void
    {
        $this->assertSame('todo-items', TodoItem::syncEntity());
        $this->assertSame('assessments', PersonAssessment::syncEntity());
        $this->assertSame('people', Person::syncEntity());
    }

    public function test_assessment_write_touches_person(): void
    {
        $person = Person::create(['name' => 'Ada', 'surname' => 'Lovelace', 'notes' => '']);
        SyncChange::query()->delete();

        $assessment = $person->assessments()->create(['source' => 'test', 'assessed_at' => now()]);

        // The assessment row is logged ...
        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'assessments',
            'record_id' => (string) $assessment->id,
            'op' => 'upsert',
        ]);
        // ... and the parent person is touched, so it is logged too.
        $this->assertDatabaseHas('sync_changes', [
            'entity' => 'people',
            'record_id' => (string) $person->id,
            'op' => 'upsert',
        ]);
    }

    public function test_cursor_is_monotonic(): void
    {
        $a = Person::create(['name' => 'A', 'surname' => 'One', 'notes' => '']);
        $b = Person::create(['name' => 'B', 'surname' => 'Two', 'notes' => '']);

        $first = SyncChange::query()->where('record_id', (string) $a->id)->min('id');
        $second = SyncChange::query()->where('record_id', (string) $b->id)->min('id');

        $this->assertGreaterThan($first, $second);
    }
}
