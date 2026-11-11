<?php

namespace App\Services\Crm;

use DOMDocument;
use DOMElement;
use DOMXPath;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Maps one Alice panel person payload (/api/kisiler/{slug}) to the person card
 * template (config/crm.php): structured scores go to their layers, HTML blocks
 * become markdown, the "approach" block is split into its three lists.
 */
class AliceCardMapper
{
    private const BIG_FIVE = [
        'açıklık' => 'openness',
        'sorumluluk' => 'conscientiousness',
        'dışadönüklük' => 'extraversion',
        'uyumluluk' => 'agreeableness',
        'duygusal denge' => 'emotional_stability',
    ];

    private const BASES = [
        'arkadaşlık' => 'friendship',
        'iş' => 'work',
        'romantik' => 'romantic',
        'cinsel' => 'sexual',
        'akrabalık' => 'kinship',
        'mentorluk' => 'mentorship',
    ];

    private const METRICS = [
        'yakınlık' => 'closeness',
        'karşılıklılık' => 'reciprocity',
        'güç dengesi' => 'power_balance',
        'güven' => 'trust',
        'çatışma yükü' => 'conflict_load',
        'momentum' => 'momentum',
    ];

    private const ATTACHMENT = [
        'güvenli' => 'secure',
        'kaygılı' => 'anxious',
        'kaçıngan' => 'avoidant',
        'dağınık' => 'disorganized',
    ];

    /** Placeholder lines Alice writes when it has nothing to say. */
    private const EMPTY_PATTERN = '/^(—|-|veri yok\.?|şu an gözlemlenmedi\.?|yok\.?)$/iu';

    private HtmlConverter $converter;

    public function __construct()
    {
        $this->converter = new HtmlConverter(['strip_tags' => true, 'header_style' => 'atx', 'hard_break' => true]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed> assessment attributes
     */
    public function assessment(array $payload): array
    {
        $meta = (array) ($payload['meta'] ?? []);
        $blocks = collect((array) ($payload['blocks'] ?? []))->keyBy('id');
        $html = fn (string $id) => (string) ($blocks[$id]['html'] ?? '');

        $history = $this->markdown($html('gecmis'));
        preg_match('/(\d[\d.]*)\s*mesaj/u', $history ?? '', $m);

        $values = $this->markdown($html('degerler'));
        $notes = collect([
            $this->markdown($html('notlar')),
            $values ? "### Değerler (Schwartz)\n\n".$values : null,
        ])->filter()->implode("\n\n");

        $attachmentText = $this->markdown($this->withoutBlockquote($html('baglanma')));

        return [
            'assessed_at' => $meta['son_degerlendirme'] ?? now()->toDateString(),
            'source' => 'import',
            'overall_confidence' => isset($meta['genel_guven']) ? round((float) $meta['genel_guven'], 2) : null,
            'relationship_label' => $meta['iliski'] ?? null,
            'big_five' => $this->bigFive((array) ($payload['bigfive'] ?? [])),
            'relationship_bases' => $this->labelled((array) ($payload['bazlar'] ?? []), self::BASES),
            'metrics' => $this->metrics((array) ($payload['metrikler'] ?? [])),
            'attachment_style' => $this->attachmentStyle($attachmentText),
            'attachment_note' => $attachmentText,
            'dark_triad_note' => $this->markdown($html('darktriad')),
            'approach' => $this->approach($html('yaklasim')),
            'summary' => $this->markdown($html('kunye')),
            'notes' => $notes !== '' ? $notes : null,
            'history_note' => $history,
            'source_message_count' => isset($m[1]) ? (int) str_replace('.', '', $m[1]) : null,
            'raw' => $payload,
        ];
    }

    /**
     * Person-level fields derived from the payload (only used to fill blanks).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function personHints(array $payload): array
    {
        $bases = collect($this->labelled((array) ($payload['bazlar'] ?? []), self::BASES) ?? [])
            ->filter(fn ($item) => ($item['score'] ?? 0) >= 40)
            ->keys()
            ->values()
            ->all();

        $professional = array_intersect($bases, ['work', 'mentorship']) !== [];
        $personal = array_diff($bases, ['work', 'mentorship']) !== [];

        return [
            'relationship_label' => $payload['meta']['iliski'] ?? null,
            'relationship_bases' => $bases ?: null,
            'relationship_scope' => match (true) {
                $professional && $personal => 'both',
                $professional => 'professional',
                $personal => 'personal',
                default => null,
            },
        ];
    }

    private function bigFive(array $data): ?array
    {
        $out = [];
        foreach ((array) ($data['labels'] ?? []) as $i => $label) {
            $key = self::BIG_FIVE[$this->norm($label)] ?? null;
            if (! $key || ! isset($data['values'][$i])) {
                continue;
            }
            $out[$key] = array_filter([
                'score' => (int) round((float) $data['values'][$i]),
                'confidence' => isset($data['conf'][$i]) ? round((float) $data['conf'][$i], 2) : null,
                'evidence' => $this->clean($data['kanit'][$i] ?? null),
            ], fn ($v) => $v !== null);
        }

        return $out ?: null;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function labelled(array $data, array $map): ?array
    {
        $out = [];
        foreach ((array) ($data['labels'] ?? []) as $i => $label) {
            $key = $map[$this->norm($label)] ?? null;
            if (! $key || ! isset($data['values'][$i])) {
                continue;
            }
            $out[$key] = array_filter([
                'score' => (int) round((float) $data['values'][$i]),
                'note' => $this->clean($data['notes'][$i] ?? null),
            ], fn ($v) => $v !== null);
        }

        return $out ?: null;
    }

    private function metrics(array $rows): ?array
    {
        $out = [];
        foreach ($rows as $row) {
            $key = self::METRICS[$this->norm((string) ($row['ad'] ?? ''))] ?? null;
            if (! $key || ! isset($row['deger'])) {
                continue;
            }
            $out[$key] = array_filter([
                'score' => (int) round((float) $row['deger']),
                'note' => $this->clean($row['metin'] ?? null),
            ], fn ($v) => $v !== null);
        }

        return $out ?: null;
    }

    private function attachmentStyle(?string $text): ?string
    {
        if (! $text || preg_match('/yetersiz|veri yok/iu', $text)) {
            return null;
        }
        $found = [];
        foreach (self::ATTACHMENT as $word => $key) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/iu', $text)) {
                $found[] = $key;
            }
        }

        return count($found) === 1 ? $found[0] : null;
    }

    /**
     * "İşe yarayan:" items → works; lists after "Tetikleyiciler:" / "Kırmızı bayraklar:" → their lists.
     */
    private function approach(string $html): ?array
    {
        if (trim($html) === '') {
            return null;
        }

        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?><div id="r">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = (new DOMXPath($dom))->query('//div[@id="r"]')->item(0);
        if (! $root) {
            return null;
        }

        $lists = ['works' => [], 'triggers' => [], 'red_flags' => []];
        $current = 'works';

        foreach ($root->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $text = trim($node->textContent);
            if ($node->tagName === 'p') {
                $lower = mb_strtolower($text);
                $current = match (true) {
                    str_contains($lower, 'tetikleyici') => 'triggers',
                    str_contains($lower, 'kırmızı bayrak') => 'red_flags',
                    default => $current,
                };

                continue;
            }
            if ($node->tagName !== 'ul') {
                continue;
            }
            foreach ($node->getElementsByTagName('li') as $li) {
                $item = trim(preg_replace('/\s+/u', ' ', $li->textContent));
                $item = preg_replace('/^([İi]şe yarayan|tetikleyici(ler)?|kırmızı bayrak(lar)?)\s*:\s*/iu', '', $item);
                if ($item !== '' && ! preg_match(self::EMPTY_PATTERN, $item)) {
                    $lists[$current][] = $item;
                }
            }
        }

        $lists = array_filter($lists);

        return $lists ?: null;
    }

    private function withoutBlockquote(string $html): string
    {
        return (string) preg_replace('#<blockquote>.*?</blockquote>#su', '', $html);
    }

    private function markdown(string $html): ?string
    {
        if (trim($html) === '') {
            return null;
        }
        $md = trim($this->converter->convert(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $plain = trim(preg_replace('/^[-*]\s+/mu', '', $md));

        return ($md === '' || preg_match(self::EMPTY_PATTERN, $plain)) ? null : $md;
    }

    private function clean(mixed $text): ?string
    {
        $text = trim((string) $text);

        return ($text === '' || preg_match(self::EMPTY_PATTERN, $text)) ? null : $text;
    }

    private function norm(string $label): string
    {
        // "İ" lowercases to "i" + combining dot in PHP; fold it back.
        return str_replace("i\u{0307}", 'i', mb_strtolower(trim($label), 'UTF-8'));
    }
}
