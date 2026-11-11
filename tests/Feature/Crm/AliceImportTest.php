<?php

use App\Models\Person;
use App\Models\PersonAssessment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Payload shaped exactly like the Alice panel's /api/kisiler/{slug} response.
 */
function aliceDetail(array $meta = []): array
{
    return [
        'meta' => array_merge([
            'slug' => 'eray-civelek', 'kisi' => 'Eray Civelek', 'iliski' => 'arkadaslik',
            'panel_id' => null, 'genel_guven' => 0.8, 'son_degerlendirme' => '2026-11-03', 'foto_url' => null,
        ], $meta),
        'bigfive' => [
            'labels' => ['Açıklık', 'Sorumluluk', 'Dışadönüklük', 'Uyumluluk', 'Duygusal Denge'],
            'values' => [58, 70, 72, 88, 75],
            'conf' => [0.5, 0.7, 0.8, 0.8, 0.7],
            'kanit' => ['pratik odaklı', 'iş+randevu takibi', 'yoğun sosyalleşme', 'neredeyse hiç çatışma yok', 'kriz anında sakin'],
        ],
        'bazlar' => [
            'labels' => ['arkadaşlık', 'iş', 'romantik', 'cinsel', 'akrabalık', 'mentorluk'],
            'values' => [92, 45, 0, 0, 0, 0],
            'notes' => ['', '', '', '', '', ''],
        ],
        'metrikler' => [
            ['ad' => 'yakınlık', 'deger' => 82, 'metin' => 'özel konuları paylaşıyor'],
            ['ad' => 'güven', 'deger' => 90, 'metin' => 'büyük borç ve sır paylaşımı'],
            ['ad' => 'çatışma yükü', 'deger' => 10, 'metin' => 'gerilim yok'],
        ],
        'blocks' => [
            ['id' => 'kunye', 'baslik' => 'Künye', 'html' => "<ul>\n<li>İlişki: arkadaslik</li>\n<li>Özet: Mahir&#x27;in en yakın arkadaşı.</li>\n</ul>"],
            ['id' => 'baglanma', 'baslik' => 'Bağlanma', 'html' => "<blockquote>\nGüvenli / Kaygılı / Kaçıngan / Dağınık.<br>\n</blockquote>\n<ul>\n<li>Net stil için yeterli gözlem yok (veri yetersiz).</li>\n</ul>"],
            ['id' => 'degerler', 'baslik' => 'Değerler', 'html' => "<ul>\n<li>Veri yok.</li>\n</ul>"],
            ['id' => 'darktriad', 'baslik' => 'Dark Triad', 'html' => "<ul>\n<li>Belirgin işaret yok.</li>\n</ul>"],
            ['id' => 'yaklasim', 'baslik' => 'Yaklaşım', 'html' => "<ul>\n<li><strong>İşe yarayan:</strong> Kriz anında yanında.</li>\n</ul>\n<p><strong>Tetikleyiciler:</strong></p>\n<ul>\n<li>İş yoğunluğu planları erteletebiliyor.</li>\n</ul>\n<p><strong>Kırmızı bayraklar:</strong></p>\n<ul>\n<li>Şu an gözlemlenmedi.</li>\n</ul>"],
            ['id' => 'notlar', 'baslik' => 'Notlar', 'html' => "<ul>\n<li>—</li>\n</ul>"],
            ['id' => 'gecmis', 'baslik' => 'Geçmiş', 'html' => "<ul>\n<li>2026-11-03: Eski relations.db&#x27;den taşındı (kaynak 938 mesaj, güven 0.8).</li>\n</ul>"],
        ],
    ];
}

beforeEach(function (): void {
    Storage::fake('local');
});

it('imports an Alice analysis onto the matching person card', function (): void {
    $person = Person::factory()->create(['name' => 'Eray', 'surname' => 'Civelek']);

    Http::fake([
        '*/api/kisiler' => Http::response([['slug' => 'eray-civelek', 'foto_url' => '/api/kisiler/eray-civelek/foto']]),
        '*/api/kisiler/eray-civelek' => Http::response(aliceDetail()),
        '*/api/kisiler/eray-civelek/foto' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png']
        ),
    ]);

    $this->artisan('crm:import-alice', ['--url' => 'http://alice.test'])->assertSuccessful();

    $person->refresh();
    $assessment = PersonAssessment::query()->firstOrFail();

    expect($person->alice_slug)->toBe('eray-civelek')
        ->and($person->relationship_scope)->toBe('both')
        ->and($person->relationship_bases)->toBe(['friendship', 'work'])
        ->and($assessment->person_id)->toBe($person->id)
        ->and($assessment->source)->toBe('import')
        ->and($assessment->overall_confidence)->toBe(0.8)
        ->and($assessment->big_five['emotional_stability'])->toBe(['score' => 75, 'confidence' => 0.7, 'evidence' => 'kriz anında sakin'])
        ->and($assessment->metrics['conflict_load']['score'])->toBe(10)
        ->and($assessment->approach)->toBe([
            'works' => ['Kriz anında yanında.'],
            'triggers' => ['İş yoğunluğu planları erteletebiliyor.'],
        ])
        ->and($assessment->attachment_style)->toBeNull()
        ->and($assessment->source_message_count)->toBe(938)
        ->and($assessment->summary)->toContain("Mahir'in en yakın arkadaşı")
        ->and($assessment->notes)->toBeNull()
        ->and($person->faces()->count())->toBe(1);

    // Running it again updates the same snapshot instead of duplicating it.
    $this->artisan('crm:import-alice', ['--url' => 'http://alice.test'])->assertSuccessful();
    expect(PersonAssessment::query()->count())->toBe(1)
        ->and($person->faces()->count())->toBe(1);
});

it('matches by panel id first and never overwrites fields entered by hand', function (): void {
    $person = Person::factory()->create(['name' => 'Farklı', 'surname' => 'İsim', 'relationship_scope' => 'personal', 'relationship_label' => 'kanka']);

    Http::fake([
        '*/api/kisiler' => Http::response([['slug' => 'eray-civelek', 'foto_url' => null]]),
        '*/api/kisiler/eray-civelek' => Http::response(aliceDetail(['panel_id' => (string) $person->id])),
    ]);

    $this->artisan('crm:import-alice', ['--url' => 'http://alice.test'])->assertSuccessful();

    $person->refresh();
    expect($person->relationship_scope)->toBe('personal')
        ->and($person->relationship_label)->toBe('kanka')
        ->and($person->assessments()->count())->toBe(1)
        ->and(Person::query()->count())->toBe(1);
});

it('changes nothing on a dry run and can import from an exported file', function (): void {
    Http::fake([
        '*/api/kisiler' => Http::response([['slug' => 'eray-civelek', 'foto_url' => null]]),
        '*/api/kisiler/eray-civelek' => Http::response(aliceDetail()),
    ]);

    $this->artisan('crm:import-alice', ['--url' => 'http://alice.test', '--dry-run' => true])->assertSuccessful();
    expect(Person::query()->count())->toBe(0)->and(PersonAssessment::query()->count())->toBe(0);

    $file = storage_path('framework/testing/alice-export.json');
    @mkdir(dirname($file), 0777, true);
    $this->artisan('crm:import-alice', ['--url' => 'http://alice.test', '--export' => $file])->assertSuccessful();
    expect(Person::query()->count())->toBe(0);

    $this->artisan('crm:import-alice', ['--from' => $file])->assertSuccessful();
    expect(Person::query()->where('alice_slug', 'eray-civelek')->exists())->toBeTrue();
    @unlink($file);
});
