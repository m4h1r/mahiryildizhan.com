<?php

namespace App\Support\Crm;

/**
 * JSON Schema (draft 2020-12) of the person card, generated from config/crm.php.
 * Served to AI agents at GET /api/v1/alice/meta/person-card-schema.
 */
final class PersonCardSchema
{
    public static function build(): array
    {
        $enum = fn (string $key) => array_keys((array) config("crm.{$key}"));
        $scored = function (string $configKey, bool $withConfidence) {
            $item = [
                'type' => 'object',
                'properties' => array_filter([
                    'score' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                    'confidence' => $withConfidence ? ['type' => 'number', 'minimum' => 0, 'maximum' => 1] : null,
                    $withConfidence ? 'evidence' : 'note' => ['type' => 'string'],
                ]),
                'required' => ['score'],
            ];

            return [
                'type' => 'object',
                'description' => 'Keys: '.implode(', ', array_keys((array) config("crm.{$configKey}"))),
                'properties' => collect(array_keys((array) config("crm.{$configKey}")))->mapWithKeys(fn ($k) => [$k => $item])->all(),
                'additionalProperties' => false,
            ];
        };
        $tags = ['type' => 'array', 'items' => ['type' => 'string', 'maxLength' => 100]];

        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$id' => url('/api/v1/alice/meta/person-card-schema'),
            'title' => 'Person card',
            'type' => 'object',
            '$defs' => [
                'person' => [
                    'type' => 'object',
                    'required' => ['name', 'surname'],
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'surname' => ['type' => 'string'],
                        'second_surname' => ['type' => ['string', 'null'], 'description' => 'Maiden name'],
                        'nickname' => ['type' => ['string', 'null']],
                        'birth_day' => ['type' => ['integer', 'null'], 'minimum' => 1, 'maximum' => 31],
                        'birth_month' => ['type' => ['integer', 'null'], 'minimum' => 1, 'maximum' => 12],
                        'birth_year' => ['type' => ['integer', 'null']],
                        'birthday' => ['type' => ['string', 'null'], 'format' => 'date', 'description' => 'Derived when day, month and year are all known. Sending it fills the parts.'],
                        'deathday' => ['type' => ['string', 'null'], 'format' => 'date'],
                        'birth_place' => ['type' => ['string', 'null']],
                        'death_place' => ['type' => ['string', 'null']],
                        'gender_id' => ['type' => ['integer', 'null'], 'description' => 'GET meta/genders'],
                        'eye_color_id' => ['type' => ['integer', 'null']],
                        'hair_color_id' => ['type' => ['integer', 'null']],
                        'blood_type_id' => ['type' => ['integer', 'null']],
                        'father_id' => ['type' => ['integer', 'null']],
                        'mother_id' => ['type' => ['integer', 'null']],
                        'partner_id' => ['type' => ['integer', 'null']],
                        'mobile' => ['type' => ['string', 'null']],
                        'email' => ['type' => ['string', 'null'], 'format' => 'email'],
                        'address' => ['type' => ['string', 'null']],
                        'notes' => ['type' => ['string', 'null']],
                        'relationship_scope' => ['enum' => [...$enum('relationship_scopes'), null]],
                        'relationship_label' => ['type' => ['string', 'null']],
                        'relationship_bases' => ['type' => 'array', 'items' => ['enum' => $enum('relationship_bases')]],
                        'company' => ['type' => ['string', 'null']],
                        'job_title' => ['type' => ['string', 'null']],
                        'stakeholder_id' => ['type' => ['integer', 'null'], 'description' => 'Linked business record (stakeholders)'],
                        'character_summary' => ['type' => ['string', 'null']],
                        'interests' => $tags,
                        'hobbies' => $tags,
                        'tendencies' => $tags,
                        'met_at' => ['type' => ['string', 'null'], 'format' => 'date'],
                        'met_how' => ['type' => ['string', 'null']],
                        'alice_slug' => ['type' => ['string', 'null'], 'pattern' => '^[A-Za-z0-9_-]+$'],
                    ],
                ],
                'channel' => [
                    'type' => 'object',
                    'required' => ['type', 'value'],
                    'properties' => [
                        'type' => ['enum' => $enum('channel_types')],
                        'value' => ['type' => 'string', 'description' => '@handle, number, e-mail or full URL'],
                        'label' => ['type' => ['string', 'null']],
                        'is_primary' => ['type' => 'boolean'],
                    ],
                ],
                'assessment' => [
                    'type' => 'object',
                    'required' => ['assessed_at'],
                    'properties' => [
                        'assessed_at' => ['type' => 'string', 'format' => 'date'],
                        'source' => ['enum' => config('crm.assessment_sources')],
                        'overall_confidence' => ['type' => ['number', 'null'], 'minimum' => 0, 'maximum' => 1],
                        'relationship_label' => ['type' => ['string', 'null']],
                        'big_five' => $scored('big_five', true),
                        'relationship_bases' => $scored('relationship_bases', false),
                        'metrics' => $scored('metrics', false),
                        'values' => $scored('schwartz_values', false),
                        'dark_triad' => $scored('dark_triad', false),
                        'dark_triad_note' => ['type' => ['string', 'null'], 'contentMediaType' => 'text/markdown'],
                        'attachment_style' => ['enum' => [...$enum('attachment_styles'), null]],
                        'attachment_note' => ['type' => ['string', 'null'], 'contentMediaType' => 'text/markdown'],
                        'approach' => [
                            'type' => 'object',
                            'properties' => collect(array_keys((array) config('crm.approach_lists')))
                                ->mapWithKeys(fn ($k) => [$k => ['type' => 'array', 'items' => ['type' => 'string']]])->all(),
                        ],
                        'summary' => ['type' => ['string', 'null'], 'contentMediaType' => 'text/markdown'],
                        'notes' => ['type' => ['string', 'null'], 'contentMediaType' => 'text/markdown'],
                        'history_note' => ['type' => ['string', 'null']],
                        'source_message_count' => ['type' => ['integer', 'null'], 'minimum' => 0],
                        'raw' => ['type' => ['object', 'null'], 'description' => 'Original payload, kept for traceability'],
                    ],
                ],
                'face' => [
                    'type' => 'object',
                    'description' => 'Biometric (KVKK special category). Send multipart `image`, or JSON with `image_base64` and/or `embedding`.',
                    'properties' => [
                        'image' => ['type' => 'string', 'contentEncoding' => 'binary', 'description' => 'multipart file (jpg/png/webp/heic); EXIF is stripped'],
                        'image_base64' => ['type' => 'string', 'contentEncoding' => 'base64'],
                        'embedding' => ['type' => 'array', 'items' => ['type' => 'number'], 'minItems' => 2, 'maxItems' => (int) config('crm.faces.max_embedding_dim')],
                        'embedding_model' => ['type' => 'string', 'description' => 'Required with embedding, e.g. arcface-r100, facenet512'],
                        'bbox' => ['type' => ['object', 'array', 'null']],
                        'landmarks' => ['type' => ['object', 'array', 'null']],
                        'quality' => ['type' => ['number', 'null'], 'minimum' => 0, 'maximum' => 1],
                        'is_primary' => ['type' => 'boolean'],
                        'source' => ['enum' => config('crm.face_sources')],
                        'captured_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    ],
                ],
            ],
            'properties' => [
                'person' => ['$ref' => '#/$defs/person'],
                'channels' => ['type' => 'array', 'items' => ['$ref' => '#/$defs/channel']],
                'latest_assessment' => ['$ref' => '#/$defs/assessment'],
                'faces' => ['type' => 'array', 'items' => ['$ref' => '#/$defs/face']],
            ],
        ];
    }
}
