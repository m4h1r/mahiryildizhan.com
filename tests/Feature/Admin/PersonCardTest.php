<?php

use App\Models\Person;
use App\Models\PersonAssessment;
use App\Models\PersonFace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

it('renders the person card with every analysis layer even without an assessment', function (): void {
    $person = Person::factory()->create();

    $this->actingAs(actingAsAdmin())
        ->get(route('admin.people.show', [$person, 'tab' => 'analysis']))
        ->assertOk()
        ->assertSee(__('No analysis yet'))
        ->assertSee(__('Face data'));
});

it('renders the latest assessment on the card', function (): void {
    $person = Person::factory()->create();
    $person->assessments()->create([
        'assessed_at' => '2026-11-03',
        'source' => 'alice',
        'overall_confidence' => 0.8,
        'big_five' => ['openness' => ['score' => 58, 'confidence' => 0.5, 'evidence' => 'pratik odaklı']],
        'metrics' => ['trust' => ['score' => 90, 'note' => 'büyük borç ve sır paylaşımı']],
        'approach' => ['works' => ['Kriz anında yanında.']],
        'summary' => '**En yakın** arkadaş',
    ]);

    $this->actingAs(actingAsAdmin())
        ->get(route('admin.people.show', [$person, 'tab' => 'analysis']))
        ->assertOk()
        ->assertSee('pratik odaklı')
        ->assertSee('büyük borç ve sır paylaşımı')
        ->assertSee('Kriz anında yanında.')
        ->assertSee('<strong>En yakın</strong>', false)
        ->assertSee('%80');
});

it('stores card fields, tags and channels from the person form', function (): void {
    $this->actingAs(actingAsAdmin())->post(route('admin.people.store'), [
        'name' => 'Eray',
        'surname' => 'Civelek',
        'nickname' => 'Kanka',
        'birth_day' => 14,
        'birth_month' => 6,
        'relationship_scope' => 'both',
        'relationship_bases' => ['friendship', 'work'],
        'interests' => ['tango', ' tenis ', 'tango', ''],
        'channels' => [
            ['type' => 'instagram', 'value' => '@eray', 'label' => '', 'is_primary' => '1'],
            ['type' => 'linkedin', 'value' => 'eray-civelek'],
        ],
    ])->assertSessionHasNoErrors();

    $person = Person::query()->where('name', 'Eray')->firstOrFail();
    expect($person->relationship_scope)->toBe('both')
        ->and($person->relationship_bases)->toBe(['friendship', 'work'])
        ->and($person->interests)->toBe(['tango', 'tenis'])
        ->and($person->birthday)->toBeNull()
        ->and($person->zodiacName())->not->toBeNull()
        ->and($person->channels)->toHaveCount(2)
        ->and($person->channels->first()->href())->toBe('https://instagram.com/eray');
});

it('removes channels left out of an update', function (): void {
    $person = Person::factory()->create();
    $keep = $person->channels()->create(['type' => 'phone', 'value' => '+90 555 000 00 00']);
    $person->channels()->create(['type' => 'x', 'value' => '@gone']);

    $this->actingAs(actingAsAdmin())->put(route('admin.people.update', $person), [
        'name' => $person->name,
        'surname' => $person->surname,
        'channels' => [['id' => $keep->id, 'type' => 'phone', 'value' => '+90 555 111 11 11']],
    ])->assertSessionHasNoErrors();

    expect($person->channels()->pluck('value')->all())->toBe(['+90 555 111 11 11']);
});

it('derives the full birthday only when day, month and year are known', function (): void {
    $person = Person::factory()->create(['birthday' => null]);

    $person->update(['birth_day' => 21, 'birth_month' => 3, 'birth_year' => 1990]);
    expect($person->fresh()->birthday->toDateString())->toBe('1990-03-21');

    $person->update(['birth_year' => null]);
    expect($person->fresh()->birthday)->toBeNull()
        ->and($person->fresh()->zodiacName())->not->toBeNull();

    $person->update(['birthday' => '1985-12-01']);
    expect($person->fresh()->only(['birth_day', 'birth_month', 'birth_year']))
        ->toBe(['birth_day' => 1, 'birth_month' => 12, 'birth_year' => 1985]);
});

it('rejects a day that does not exist in the month', function (): void {
    $this->actingAs(actingAsAdmin())->post(route('admin.people.store'), [
        'name' => 'A', 'surname' => 'B', 'birth_day' => 31, 'birth_month' => 2,
    ])->assertSessionHasErrors('birth_day');
});

it('creates, edits and deletes an assessment snapshot', function (): void {
    $admin = actingAsAdmin();
    $person = Person::factory()->create();

    $this->actingAs($admin)->get(route('admin.people.assessments.create', $person))->assertOk();

    $this->actingAs($admin)->post(route('admin.people.assessments.store', $person), [
        'assessed_at' => '2026-11-12',
        'overall_confidence' => 0.6,
        'big_five' => ['openness' => ['score' => 70, 'confidence' => 0.4, 'evidence' => 'meraklı']],
        'metrics' => ['trust' => ['score' => 55]],
        'approach_text' => ['works' => "Açık konuş\n\nSöz ver ve tut", 'triggers' => '', 'red_flags' => ''],
    ])->assertRedirect();

    $assessment = PersonAssessment::query()->firstOrFail();
    expect($assessment->big_five['openness']['score'])->toBe(70)
        ->and($assessment->approach)->toBe(['works' => ['Açık konuş', 'Söz ver ve tut']])
        ->and($assessment->source)->toBe('manual');

    $this->actingAs($admin)->put(route('admin.people.assessments.update', [$person, $assessment]), [
        'assessed_at' => '2026-11-12',
        'metrics' => ['trust' => ['score' => 75]],
    ])->assertRedirect();
    expect($assessment->fresh()->metrics['trust']['score'])->toBe(75);

    $this->actingAs($admin)->delete(route('admin.people.assessments.destroy', [$person, $assessment]))->assertRedirect();
    $this->assertSoftDeleted('person_assessments', ['id' => $assessment->id]);
});

it('keeps face images private and embeddings encrypted', function (): void {
    $admin = actingAsAdmin();
    $person = Person::factory()->create();

    $this->actingAs($admin)->post(route('admin.people.faces.store', $person), [
        'image' => UploadedFile::fake()->image('face.jpg', 400, 400),
        'embedding_json' => json_encode(['embedding' => [0.125, -0.5, 0.75], 'embedding_model' => 'arcface-r100']),
    ])->assertSessionHasNoErrors();

    $face = PersonFace::query()->firstOrFail();
    expect($face->embedding)->toBe([0.125, -0.5, 0.75])
        ->and($face->embedding_dim)->toBe(3)
        ->and($face->is_primary)->toBeTrue()
        ->and($face->image_path)->toEndWith('.webp');
    Storage::disk('local')->assertExists($face->image_path);

    $raw = DB::table('person_faces')->value('embedding');
    expect($raw)->not->toContain('0.125')
        ->and($face->toArray())->not->toHaveKey('embedding');

    // Guests are sent to login, non-admins are refused, admins get the image.
    auth()->logout();
    $this->get(route('admin.people.faces.image', [$person, $face]))->assertRedirect(route('login'));
    $this->actingAs(actingAsNonAdmin())->get(route('admin.people.faces.image', [$person, $face]))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.people.faces.image', [$person, $face]))->assertOk();

    $this->actingAs($admin)->delete(route('admin.people.faces.destroy', [$person, $face]))->assertRedirect();
    Storage::disk('local')->assertMissing($face->image_path);
});

it('rejects face data that is not a JSON array', function (): void {
    $person = Person::factory()->create();

    $this->actingAs(actingAsAdmin())->post(route('admin.people.faces.store', $person), [
        'embedding_json' => '{"foo": 1}',
    ])->assertSessionHasErrors('embedding_json');
});

it('filters people by relationship scope and missing analysis', function (): void {
    $admin = actingAsAdmin();
    $personal = Person::factory()->create(['name' => 'Personal', 'relationship_scope' => 'personal']);
    Person::factory()->create(['name' => 'Work', 'relationship_scope' => 'professional']);
    $both = Person::factory()->create(['name' => 'Both', 'relationship_scope' => 'both']);
    $both->assessments()->create(['assessed_at' => now()]);

    $this->actingAs($admin)->get(route('admin.people.index', ['scope' => 'personal']))
        ->assertOk()
        ->assertViewHas('people', fn ($people) => $people->pluck('name')->sort()->values()->all() === ['Both', 'Personal']);

    $this->actingAs($admin)->get(route('admin.people.index', ['missing_assessment' => 1]))
        ->assertViewHas('people', fn ($people) => ! $people->pluck('name')->contains('Both'));
});

it('hard-deleting a person removes its card data and face files', function (): void {
    $person = Person::factory()->create();
    $person->channels()->create(['type' => 'x', 'value' => '@a']);
    $person->assessments()->create(['assessed_at' => now()]);
    Storage::disk('local')->put('people-faces/'.$person->id.'/a.webp', 'x');
    $person->faces()->create(['image_path' => 'people-faces/'.$person->id.'/a.webp']);

    $person->forceDelete();

    expect(DB::table('person_channels')->count())->toBe(0)
        ->and(DB::table('person_assessments')->count())->toBe(0)
        ->and(DB::table('person_faces')->count())->toBe(0);
    Storage::disk('local')->assertMissing('people-faces/'.$person->id.'/a.webp');
});
