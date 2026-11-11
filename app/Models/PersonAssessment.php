<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * One personality / relationship evaluation snapshot of a person.
 *
 * JSON layers (keys come from config/crm.php):
 *  - big_five:            {key: {score 0–100, confidence 0–1, evidence}}
 *  - relationship_bases:  {key: {score 0–100, note}}
 *  - metrics:             {key: {score 0–100, note}}
 *  - values (Schwartz):   {key: {score 0–100, note}}
 *  - dark_triad:          {key: {score 0–100, note}}
 *  - approach:            {works: [], triggers: [], red_flags: []}
 */
/**
 * @property int $id
 * @property int $person_id
 * @property Carbon|null $assessed_at
 * @property string $source
 * @property float|null $overall_confidence
 * @property array<string, array<string, mixed>>|null $big_five
 * @property array<string, array<string, mixed>>|null $relationship_bases
 * @property array<string, array<string, mixed>>|null $metrics
 * @property array<string, array<string, mixed>>|null $values
 * @property array<string, array<string, mixed>>|null $dark_triad
 * @property array<string, array<int, string>>|null $approach
 * @property array<string, mixed>|null $raw
 */
class PersonAssessment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    public const LAYERS = ['big_five', 'relationship_bases', 'metrics', 'values', 'dark_triad'];

    protected $fillable = [
        'person_id', 'assessed_at', 'source', 'overall_confidence', 'relationship_label',
        'big_five', 'relationship_bases', 'metrics', 'attachment_style', 'attachment_note',
        'values', 'dark_triad', 'dark_triad_note', 'approach', 'summary', 'notes',
        'history_note', 'source_message_count', 'raw', 'created_by',
    ];

    protected $hidden = ['raw'];

    protected function casts(): array
    {
        return [
            'assessed_at' => 'date',
            'overall_confidence' => 'float',
            'big_five' => 'array',
            'relationship_bases' => 'array',
            'metrics' => 'array',
            'values' => 'array',
            'dark_triad' => 'array',
            'approach' => 'array',
            'raw' => 'array',
            'source_message_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * A layer as an ordered list of rows for display, following the config order.
     *
     * @return array<int, array{key: string, label: string, score: ?int, confidence: ?float, text: ?string}>
     */
    public function layerRows(string $layer): array
    {
        $configKey = $layer === 'values' ? 'schwartz_values' : $layer;
        $data = (array) ($this->{$layer} ?? []);
        $rows = [];

        foreach ((array) config("crm.{$configKey}", []) as $key => $label) {
            $item = $data[$key] ?? null;
            if (! is_array($item)) {
                continue;
            }
            $rows[] = [
                'key' => $key,
                'label' => __($label),
                'score' => isset($item['score']) ? (int) round((float) $item['score']) : null,
                'confidence' => isset($item['confidence']) ? (float) $item['confidence'] : null,
                'text' => $item['evidence'] ?? $item['note'] ?? null,
            ];
        }

        return $rows;
    }

    public function hasLayer(string $layer): bool
    {
        return collect($this->layerRows($layer))->contains(fn ($row) => $row['score'] !== null);
    }

    /**
     * @return array<int, string>
     */
    public function approachList(string $key): array
    {
        $approach = $this->approach ?? [];
        $items = $approach[$key] ?? [];

        return array_values(array_filter(array_map('strval', $items), fn (string $item) => $item !== ''));
    }

    public static function markdown(?string $text): ?HtmlString
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        return new HtmlString(Str::markdown($text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
