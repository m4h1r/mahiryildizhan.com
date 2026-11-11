<?php

/*
|--------------------------------------------------------------------------
| Person card template (CRM + personality inventory)
|--------------------------------------------------------------------------
| The single source for the person card: admin forms, validation rules, the
| Alice API and the JSON Schema served at /api/v1/alice/meta/person-card-schema
| all read these lists. Keys are stable English slugs; labels are translated
| through lang/{tr,en}.json ("crm.<group>.<key>" is NOT used — the label text
| below is the translation key).
|
| The assessment layers mirror the five-layer evaluation Alice already
| produces (Big Five, relationship bases, relationship metrics, attachment /
| values / dark triad, approach), so an Alice payload maps 1:1.
*/

return [
    'relationship_scopes' => [
        'personal' => 'Personal',
        'professional' => 'Professional',
        'both' => 'Personal & professional',
    ],

    'relationship_bases' => [
        'friendship' => 'Friendship',
        'work' => 'Work',
        'romantic' => 'Romantic',
        'sexual' => 'Sexual',
        'kinship' => 'Kinship',
        'mentorship' => 'Mentorship',
    ],

    'channel_types' => [
        'phone' => 'Phone',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'instagram' => 'Instagram',
        'x' => 'X',
        'linkedin' => 'LinkedIn',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'github' => 'GitHub',
        'website' => 'Website',
        'other' => 'Other',
    ],

    // Big Five — score 0–100, confidence 0–1, evidence text
    'big_five' => [
        'openness' => 'Openness',
        'conscientiousness' => 'Conscientiousness',
        'extraversion' => 'Extraversion',
        'agreeableness' => 'Agreeableness',
        'emotional_stability' => 'Emotional stability',
    ],

    // Relationship quality metrics — score 0–100, note
    'metrics' => [
        'closeness' => 'Closeness',
        'reciprocity' => 'Reciprocity',
        'power_balance' => 'Power balance',
        'trust' => 'Trust',
        'conflict_load' => 'Conflict load',
        'momentum' => 'Momentum',
    ],

    'attachment_styles' => [
        'secure' => 'Secure',
        'anxious' => 'Anxious',
        'avoidant' => 'Avoidant',
        'disorganized' => 'Disorganized',
    ],

    // Schwartz basic human values — score 0–100, note
    'schwartz_values' => [
        'self_direction' => 'Self-direction',
        'stimulation' => 'Stimulation',
        'hedonism' => 'Hedonism',
        'achievement' => 'Achievement',
        'power' => 'Power',
        'security' => 'Security',
        'conformity' => 'Conformity',
        'tradition' => 'Tradition',
        'benevolence' => 'Benevolence',
        'universalism' => 'Universalism',
    ],

    // Dark triad — score 0–100, note
    'dark_triad' => [
        'narcissism' => 'Narcissism',
        'machiavellianism' => 'Machiavellianism',
        'psychopathy' => 'Psychopathy',
    ],

    'approach_lists' => [
        'works' => 'What works',
        'triggers' => 'Triggers',
        'red_flags' => 'Red flags',
    ],

    'assessment_sources' => ['manual', 'alice', 'import'],

    'face_sources' => ['manual', 'alice', 'import'],

    // Faces are biometric data (KVKK special category): private disk only.
    'faces' => [
        'disk' => env('CRM_FACES_DISK', 'local'),
        'directory' => 'people-faces',
        'max_kb' => 8192,
        'max_embedding_dim' => 4096,
    ],

    // Alice panel (Tailscale) — only used by `php artisan crm:import-alice`.
    'alice_panel_url' => env('ALICE_PANEL_URL', 'http://100.68.67.4:8770'),
];
