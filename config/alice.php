<?php

return [
    'allowed_ips' => array_filter(
        explode(',', env('ALICE_ALLOWED_IPS', ''))
    ),

    'rate_limit' => (int) env('ALICE_RATE_LIMIT', 120),

    'audit_retention_days' => (int) env('ALICE_AUDIT_RETENTION_DAYS', 365),

    'token_name' => 'alice-bridge',
];
