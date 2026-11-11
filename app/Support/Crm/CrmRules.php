<?php

namespace App\Support\Crm;

use Illuminate\Validation\Rule;

/**
 * Validation rules for the person card, shared by the admin forms and the
 * Alice API so both sides accept exactly the same template (config/crm.php).
 */
final class CrmRules
{
    /**
     * CRM fields on the people table.
     *
     * @return array<string, mixed>
     */
    public static function person(?int $ignoreId = null): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:255'],
            'birth_day' => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'integer', 'between:1850,'.(now()->year + 1)],
            'relationship_scope' => ['nullable', Rule::in(array_keys(config('crm.relationship_scopes')))],
            'relationship_label' => ['nullable', 'string', 'max:255'],
            'relationship_bases' => ['nullable', 'array'],
            'relationship_bases.*' => ['string', Rule::in(array_keys(config('crm.relationship_bases')))],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'stakeholder_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'character_summary' => ['nullable', 'string', 'max:20000'],
            'interests' => ['nullable', 'array', 'max:100'],
            'interests.*' => ['nullable', 'string', 'max:100'],
            'hobbies' => ['nullable', 'array', 'max:100'],
            'hobbies.*' => ['nullable', 'string', 'max:100'],
            'tendencies' => ['nullable', 'array', 'max:100'],
            'tendencies.*' => ['nullable', 'string', 'max:100'],
            'met_at' => ['nullable', 'date'],
            'met_how' => ['nullable', 'string', 'max:255'],
            'alice_slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('people', 'alice_slug')->ignore($ignoreId)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function channel(string $prefix = ''): array
    {
        return [
            $prefix.'type' => ['required', Rule::in(array_keys(config('crm.channel_types')))],
            $prefix.'value' => ['required', 'string', 'max:500'],
            $prefix.'label' => ['nullable', 'string', 'max:255'],
            $prefix.'is_primary' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function assessment(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        $rules = [
            'assessed_at' => [$required, 'date'],
            'source' => ['nullable', Rule::in(config('crm.assessment_sources'))],
            'overall_confidence' => ['nullable', 'numeric', 'between:0,1'],
            'relationship_label' => ['nullable', 'string', 'max:255'],
            'attachment_style' => ['nullable', Rule::in(array_keys(config('crm.attachment_styles')))],
            'attachment_note' => ['nullable', 'string', 'max:20000'],
            'dark_triad_note' => ['nullable', 'string', 'max:20000'],
            'approach' => ['nullable', 'array'],
            'summary' => ['nullable', 'string', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:100000'],
            'history_note' => ['nullable', 'string', 'max:20000'],
            'source_message_count' => ['nullable', 'integer', 'min:0'],
            'raw' => ['nullable', 'array'],
        ];

        foreach (array_keys(config('crm.approach_lists')) as $list) {
            $rules["approach.{$list}"] = ['nullable', 'array', 'max:50'];
            $rules["approach.{$list}.*"] = ['nullable', 'string', 'max:2000'];
        }

        foreach (self::layers() as $layer => $configKey) {
            $keys = array_keys(config("crm.{$configKey}"));
            $rules[$layer] = ['nullable', 'array'];
            foreach ($keys as $key) {
                $rules["{$layer}.{$key}"] = ['nullable', 'array'];
                $rules["{$layer}.{$key}.score"] = ['nullable', 'numeric', 'between:0,100'];
                $rules["{$layer}.{$key}.confidence"] = ['nullable', 'numeric', 'between:0,1'];
                $rules["{$layer}.{$key}.evidence"] = ['nullable', 'string', 'max:5000'];
                $rules["{$layer}.{$key}.note"] = ['nullable', 'string', 'max:5000'];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function face(bool $requireSomething = true): array
    {
        $maxDim = (int) config('crm.faces.max_embedding_dim');

        return [
            'image' => [$requireSomething ? 'required_without:embedding' : 'nullable', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:'.config('crm.faces.max_kb')],
            'embedding' => [$requireSomething ? 'required_without:image' : 'nullable', 'nullable', 'array', 'min:2', "max:{$maxDim}"],
            'embedding.*' => ['numeric'],
            'embedding_model' => ['nullable', 'string', 'max:100', 'required_with:embedding'],
            'bbox' => ['nullable', 'array'],
            'landmarks' => ['nullable', 'array'],
            'quality' => ['nullable', 'numeric', 'between:0,1'],
            'is_primary' => ['nullable', 'boolean'],
            'source' => ['nullable', Rule::in(config('crm.face_sources'))],
            'captured_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Assessment layer column => config list key.
     *
     * @return array<string, string>
     */
    public static function layers(): array
    {
        return [
            'big_five' => 'big_five',
            'relationship_bases' => 'relationship_bases',
            'metrics' => 'metrics',
            'values' => 'schwartz_values',
            'dark_triad' => 'dark_triad',
        ];
    }

    /**
     * Drop empty items from layer arrays (unset sliders, blank evidence) so the
     * stored JSON only holds what was actually assessed.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function cleanAssessment(array $data): array
    {
        foreach (array_keys(self::layers()) as $layer) {
            if (! array_key_exists($layer, $data)) {
                continue;
            }
            $clean = [];
            foreach ((array) $data[$layer] as $key => $item) {
                $item = array_filter((array) $item, fn ($v) => $v !== null && $v !== '');
                if ($item === [] || ! array_key_exists('score', $item)) {
                    continue;
                }
                $item['score'] = (int) round((float) $item['score']);
                if (isset($item['confidence'])) {
                    $item['confidence'] = round((float) $item['confidence'], 2);
                }
                $clean[$key] = $item;
            }
            $data[$layer] = $clean ?: null;
        }

        if (array_key_exists('approach', $data)) {
            $approach = [];
            foreach ((array) $data['approach'] as $list => $items) {
                $items = array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $items)));
                if ($items !== []) {
                    $approach[$list] = $items;
                }
            }
            $data['approach'] = $approach ?: null;
        }

        return $data;
    }

    /**
     * Normalise tag lists (interests, hobbies, tendencies): trim, de-dupe, drop blanks.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function cleanTags(array $data): array
    {
        foreach (['interests', 'hobbies', 'tendencies', 'relationship_bases'] as $field) {
            if (array_key_exists($field, $data)) {
                $items = array_map(fn ($v) => trim((string) $v), (array) ($data[$field] ?? []));
                $items = array_values(array_unique(array_filter($items, fn ($v) => $v !== '')));
                $data[$field] = $items ?: null;
            }
        }

        return $data;
    }
}
