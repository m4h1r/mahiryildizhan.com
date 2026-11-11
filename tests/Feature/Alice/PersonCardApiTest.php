<?php

namespace Tests\Feature\Alice;

use App\Models\AliceAuditLog;
use App\Models\IdempotencyKey;
use App\Models\Person;
use App\Models\PersonFace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PersonCardApiTest extends AliceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function person(array $attributes = []): Person
    {
        return Person::create(['name' => 'Eray', 'surname' => 'Civelek', 'notes' => '', ...$attributes]);
    }

    public function test_schema_describes_the_card_template(): void
    {
        $this->aliceGet('meta/person-card-schema')
            ->assertOk()
            ->assertJsonPath('title', 'Person card')
            ->assertJsonPath('$defs.assessment.properties.big_five.properties.openness.required', ['score'])
            ->assertJsonPath('$defs.person.properties.relationship_scope.enum', ['personal', 'professional', 'both', null]);
    }

    public function test_store_accepts_card_fields(): void
    {
        $this->alicePost('people', [
            'name' => 'Yeşim',
            'surname' => 'Koçak',
            'birth_day' => 2,
            'birth_month' => 9,
            'relationship_scope' => 'personal',
            'relationship_bases' => ['friendship'],
            'hobbies' => ['yoga'],
            'alice_slug' => 'yesim-kocak',
        ])->assertCreated()
            ->assertJsonPath('data.relationship_scope', 'personal')
            ->assertJsonPath('data.hobbies', ['yoga'])
            ->assertJsonPath('data.birth_month', 9)
            ->assertJsonPath('data.birthday', null);

        $this->alicePost('people', ['name' => 'X', 'surname' => 'Y', 'relationship_scope' => 'enemy'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_filters_by_missing_assessment_and_slug(): void
    {
        $assessed = $this->person(['alice_slug' => 'eray-civelek']);
        $assessed->assessments()->create(['assessed_at' => now()]);
        $this->person(['name' => 'Laura', 'surname' => 'Rotalinti']);

        $this->aliceGet('people', ['missing_assessment' => 1])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Laura');

        $this->aliceGet('people', ['alice_slug' => 'eray-civelek', 'include' => 'latest_assessment'])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.latest_assessment.person_id', $assessed->id);
    }

    public function test_assessment_snapshots_and_card(): void
    {
        $person = $this->person();

        $this->alicePost("people/{$person->id}/assessments", [
            'assessed_at' => '2026-11-03',
            'overall_confidence' => 0.8,
            'big_five' => ['openness' => ['score' => 58, 'confidence' => 0.5, 'evidence' => 'pratik odaklı']],
            'relationship_bases' => ['friendship' => ['score' => 92], 'work' => ['score' => 35]],
            'approach' => ['works' => ['Kriz anında yanında.', ''], 'red_flags' => []],
        ])->assertCreated()
            ->assertJsonPath('data.source', 'alice')
            ->assertJsonPath('data.big_five.openness.score', 58)
            ->assertJsonPath('data.approach.works', ['Kriz anında yanında.']);

        $this->alicePost("people/{$person->id}/assessments", [
            'assessed_at' => '2026-11-10',
            'metrics' => ['trust' => ['score' => 91]],
        ])->assertCreated();

        $this->aliceGet("people/{$person->id}/assessments")->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.assessed_at', '2026-11-10');

        $this->aliceGet("people/{$person->id}/card")
            ->assertOk()
            ->assertJsonPath('data.latest_assessment.assessed_at', '2026-11-10')
            ->assertJsonPath('data.channels', [])
            ->assertJsonPath('data.faces', []);

        $this->alicePost("people/{$person->id}/assessments", [
            'assessed_at' => '2026-11-11',
            'big_five' => ['openness' => ['score' => 140]],
        ])->assertStatus(422);
    }

    public function test_nested_writes_are_audited_under_their_own_table(): void
    {
        $person = $this->person();
        $id = $this->alicePost("people/{$person->id}/assessments", ['assessed_at' => '2026-11-03'])->json('data.id');

        $this->alicePatch("people/{$person->id}/assessments/{$id}", ['metrics' => ['trust' => ['score' => 70]]])->assertOk();

        $this->assertSame(
            ['person_assessments', 'person_assessments'],
            AliceAuditLog::query()->orderBy('id')->pluck('table_name')->all()
        );
    }

    public function test_nested_routes_are_scoped_to_the_person(): void
    {
        $a = $this->person();
        $b = $this->person(['name' => 'Other']);
        $assessment = $a->assessments()->create(['assessed_at' => now()]);

        $this->aliceGet("people/{$b->id}/assessments/{$assessment->id}")->assertNotFound();
        $this->aliceDelete("people/{$b->id}/assessments/{$assessment->id}")->assertNotFound();
        $this->assertNotSoftDeleted($assessment);
    }

    public function test_channels_crud(): void
    {
        $person = $this->person();

        $id = $this->alicePost("people/{$person->id}/channels", ['type' => 'instagram', 'value' => '@eray'])
            ->assertCreated()
            ->assertJsonPath('data.url', 'https://instagram.com/eray')
            ->json('data.id');

        $this->alicePatch("people/{$person->id}/channels/{$id}", ['label' => 'kişisel'])
            ->assertOk()->assertJsonPath('data.label', 'kişisel');

        $this->aliceDelete("people/{$person->id}/channels/{$id}")->assertOk();
        $this->aliceGet("people/{$person->id}/channels")->assertJsonCount(0, 'data');
    }

    public function test_face_embedding_is_only_returned_on_explicit_get(): void
    {
        $person = $this->person();
        $embedding = [0.125, -0.5, 0.75, 0.25];

        $response = $this->alicePost("people/{$person->id}/faces", [
            'embedding' => $embedding,
            'embedding_model' => 'arcface-r100',
            'quality' => 0.9,
        ], ['Idempotency-Key' => 'face-1'])->assertCreated()
            ->assertJsonPath('data.embedding_dim', 4)
            ->assertJsonPath('data.is_primary', true)
            ->assertJsonMissingPath('data.embedding');

        $faceId = $response->json('data.id');

        // Not in the audit log, not in the idempotency replay store.
        $this->assertStringNotContainsString('0.125', json_encode(AliceAuditLog::query()->get()->toArray()));
        $this->assertStringNotContainsString('0.125', json_encode(IdempotencyKey::query()->get()->toArray()));

        $this->aliceGet("people/{$person->id}/faces")->assertJsonMissingPath('data.0.embedding');
        $this->aliceGet("people/{$person->id}/faces/{$faceId}", ['with_embedding' => 1])
            ->assertJsonPath('data.embedding', $embedding);

        $this->aliceDelete("people/{$person->id}/faces/{$faceId}")->assertOk();
        $this->assertStringNotContainsString('0.125', json_encode(AliceAuditLog::query()->get()->toArray()));
    }

    public function test_face_image_upload_is_private_and_streamable(): void
    {
        $person = $this->person();

        $id = $this->post("/api/v1/alice/people/{$person->id}/faces", [
            'image' => UploadedFile::fake()->image('yuz.jpg', 300, 300),
        ], $this->aliceHeaders())->assertCreated()->assertJsonPath('data.has_image', true)->json('data.id');

        $face = PersonFace::findOrFail($id);
        Storage::disk('local')->assertExists($face->image_path);

        $this->get("/api/v1/alice/people/{$person->id}/faces/{$id}/image", $this->aliceHeaders())
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');
        $this->get("/api/v1/alice/people/{$person->id}/faces/{$id}/image")->assertUnauthorized();

        $this->alicePost("people/{$person->id}/faces", [])->assertStatus(422);
    }

    public function test_dry_run_face_upload_leaves_no_file(): void
    {
        $person = $this->person();

        $this->post("/api/v1/alice/people/{$person->id}/faces?dry_run=1", [
            'image' => UploadedFile::fake()->image('yuz.jpg', 200, 200),
        ], $this->aliceHeaders())->assertCreated();

        $this->assertSame(0, PersonFace::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_update_keeps_alice_slug_unique(): void
    {
        $this->person(['alice_slug' => 'taken']);
        $other = $this->person(['name' => 'Other']);

        $this->alicePatch("people/{$other->id}", ['alice_slug' => 'taken'])->assertStatus(422);
        $this->alicePatch("people/{$other->id}", ['alice_slug' => 'free'])->assertOk();
    }
}
