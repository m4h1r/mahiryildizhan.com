<?php

use App\Models\Comment;
use App\Models\Subscriber;

/*
|--------------------------------------------------------------------------
| Data retention policy (S17 — KVKK Art.7)
|--------------------------------------------------------------------------
| Every table that stores personal data declares how long it is kept. A row
| past its window is a KVKK violation, not just clutter, so `data:prune`
| (scheduled daily) enforces these. Keep these periods in sync with the KVKK
| Aydınlatma Metni (E11) — the notice legally states them.
|
| Each policy:
|   model        the Eloquent model
|   timestamp    column the age is measured from
|   days         keep this many days past `timestamp`, then prune
|   where        optional equality filters narrowing which rows prune
|   only_trashed only soft-deleted rows (requires SoftDeletes)
|   force        permanently delete (forceDelete) instead of soft delete
*/

return [
    'policies' => [
        // Unsubscribed newsletter e-mails: no consent remains, so purge 90
        // days after unsubscribe (short grace for accidental unsubscribes).
        'subscribers_unsubscribed' => [
            'model' => Subscriber::class,
            'timestamp' => 'unsubscribed_at',
            'days' => 90,
            'where' => ['status' => 'unsubscribed'],
            'force' => true,
            'description' => 'Aboneliği iptal edilmiş e-postalar 90 gün sonra kalıcı silinir.',
        ],

        // Unapproved comments carry guest name/e-mail with no published value.
        'comments_unapproved' => [
            'model' => Comment::class,
            'timestamp' => 'created_at',
            'days' => 180,
            'where' => ['is_approved' => false],
            'force' => true,
            'description' => 'Onaylanmamış yorumlar (ad/e-posta) 180 gün sonra kalıcı silinir.',
        ],

        // Soft-deleted comments: 30-day recovery window, then purge.
        'comments_trashed' => [
            'model' => Comment::class,
            'timestamp' => 'deleted_at',
            'days' => 30,
            'only_trashed' => true,
            'force' => true,
            'description' => 'Çöpe atılmış yorumlar 30 gün sonra kalıcı silinir.',
        ],
    ],
];
