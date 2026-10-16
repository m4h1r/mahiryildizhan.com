<?php

/**
 * Translation key parity gate (T3).
 *
 * Compares tr <-> en across both the JSON catalogues (lang/tr.json,
 * lang/en.json) and the PHP group files (lang/tr/*.php, lang/en/*.php),
 * reporting every key present in one locale but missing in the other.
 *
 * Exit code is non-zero on any mismatch so it can run as a CI step.
 *
 *   php scripts/lang-parity.php
 */
$root = dirname(__DIR__);
$errors = [];

/** Flatten a nested array into dot keys. */
function flatten(array $data, string $prefix = ''): array
{
    $flat = [];
    foreach ($data as $key => $value) {
        $full = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
        if (is_array($value)) {
            $flat += flatten($value, $full);
        } else {
            $flat[$full] = true;
        }
    }

    return $flat;
}

function compare(string $label, array $tr, array $en, array &$errors): void
{
    $missingInEn = array_diff_key($tr, $en);
    $missingInTr = array_diff_key($en, $tr);

    foreach (array_keys($missingInEn) as $key) {
        $errors[] = "[{$label}] missing in en: {$key}";
    }
    foreach (array_keys($missingInTr) as $key) {
        $errors[] = "[{$label}] missing in tr: {$key}";
    }
}

// 1. JSON catalogues
$trJson = $root.'/lang/tr.json';
$enJson = $root.'/lang/en.json';
if (is_file($trJson) && is_file($enJson)) {
    $tr = flatten(json_decode(file_get_contents($trJson), true) ?: []);
    $en = flatten(json_decode(file_get_contents($enJson), true) ?: []);
    compare('json', $tr, $en, $errors);
}

// 2. PHP group files
$trDir = $root.'/lang/tr';
$enDir = $root.'/lang/en';
if (is_dir($trDir) && is_dir($enDir)) {
    $files = array_unique(array_merge(
        array_map('basename', glob($trDir.'/*.php') ?: []),
        array_map('basename', glob($enDir.'/*.php') ?: []),
    ));
    sort($files);

    foreach ($files as $file) {
        $trData = is_file($trDir.'/'.$file) ? (require $trDir.'/'.$file) : [];
        $enData = is_file($enDir.'/'.$file) ? (require $enDir.'/'.$file) : [];
        compare($file, flatten($trData), flatten($enData), $errors);
    }
}

if ($errors === []) {
    fwrite(STDOUT, "✓ lang parity OK (tr <-> en)\n");
    exit(0);
}

fwrite(STDERR, '✗ lang parity FAILED — '.count($errors)." key(s):\n");
foreach ($errors as $line) {
    fwrite(STDERR, "  {$line}\n");
}
exit(1);
