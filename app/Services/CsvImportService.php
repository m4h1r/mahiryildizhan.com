<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CsvImportService
{
    private const DEFAULT_EXPENSE_TYPES = [
        'Gida', 'Ulasim', 'Konaklama', 'Yazilim', 'Diger',
        'Market', 'Fuel', 'Meal', 'Collection', 'Rent', 'Bill', 'Health',
    ];

    private const BLOOD_TYPE_ALIASES = [
        'A+' => 'A Rh+', 'A-' => 'A Rh-',
        'B+' => 'B Rh+', 'B-' => 'B Rh-',
        'AB+' => 'AB Rh+', 'AB-' => 'AB Rh-',
        '0+' => '0 Rh+', '0-' => '0 Rh-',
        'O+' => '0 Rh+', 'O-' => '0 Rh-',
    ];

    private const TABLES = [
        'genders' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'eye_colors' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'blood_types' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'hair_colors' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'post_categories' => [
            'upsert' => ['slug'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'post_languages' => [
            'upsert' => ['code'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'income_sources' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'income_types' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'currencies' => [
            'upsert' => ['code'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'expense_types' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'interaction_types' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'stakeholders' => [
            'upsert' => ['vkn_tckn'],
            'helper_columns' => ['user_email'],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'people' => [
            'upsert' => ['id'],
            'helper_columns' => [
                'gender_name',
                'gender_slug',
                'eye_color_name',
                'eye_color_slug',
                'blood_type_name',
                'hair_color_name',
                'hair_color_slug',
            ],
            'boolean_columns' => [],
            'json_columns' => [],
            'column_aliases' => [
                'eye_color' => 'eye_color_name',
                'hair_color' => 'hair_color_name',
                'gender' => 'gender_id',
                'blood_type' => 'blood_type_name',
            ],
            'ignore_columns' => [],
            'preserve_id' => true,
        ],
        'posts' => [
            'upsert' => ['slug'],
            'helper_columns' => ['category_slug', 'language_code', 'user_email'],
            'boolean_columns' => ['published'],
            'json_columns' => [],
            'column_aliases' => [
                'language' => 'language_id',
                'views' => 'view_count',
            ],
            'ignore_columns' => ['keywords'],
        ],
        'comments' => [
            'upsert' => ['post_id', 'guest_email', 'created_at'],
            'helper_columns' => ['post_slug', 'user_email'],
            'boolean_columns' => ['is_approved'],
            'json_columns' => [],
        ],
        'incomes' => [
            'upsert' => ['date', 'amount', 'income_source_id'],
            'helper_columns' => ['income_source_name', 'income_type_name', 'currency_code', 'currency_symbol', 'user_email'],
            'boolean_columns' => [],
            'json_columns' => [],
            'column_aliases' => [
                'source' => 'income_source_name',
                'currency' => 'currency_symbol',
                'type' => 'income_type_name',
            ],
            'ignore_columns' => [],
        ],
        'expenses' => [
            'upsert' => ['id'],
            'helper_columns' => ['stakeholder_vkn', 'expense_type_name', 'currency_code', 'currency_symbol'],
            'boolean_columns' => ['company_expense', 'paid_by_others'],
            'json_columns' => [],
            'column_aliases' => [
                'type' => 'expense_type_name',
                'currency' => 'currency_symbol',
            ],
            'ignore_columns' => ['seller', 'expense_category_id'],
            'preserve_id' => true,
        ],
        'interactions' => [
            'upsert' => ['person_id', 'date', 'interaction_type_id'],
            'helper_columns' => ['person_name', 'person_surname', 'person_birthday', 'interaction_type_name'],
            'boolean_columns' => [],
            'json_columns' => [],
            'column_aliases' => [
                'in_date' => 'date',
                'type' => 'interaction_type_id',
            ],
            'ignore_columns' => [],
            'preserve_id' => true,
        ],
        'timeline_events' => [
            'upsert' => ['title', 'start_date'],
            'helper_columns' => [],
            'boolean_columns' => ['is_public'],
            'json_columns' => ['tags', 'metadata'],
        ],
        'adages' => [
            'upsert' => ['adage'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'nodes' => [
            'upsert' => ['name'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'node_connections' => [
            'upsert' => ['node_from_id', 'node_to_id'],
            'helper_columns' => ['node_from', 'node_to'],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
        'subscribers' => [
            'upsert' => ['email'],
            'helper_columns' => [],
            'boolean_columns' => [],
            'json_columns' => [],
        ],
    ];

    public function import(string $table, bool $dryRun = false): array
    {
        $config = $this->config($table);
        $path = $this->resolvePath($table);
        $foreignKeyChecksDisabled = false;

        if ($path === null) {
            return $this->reportWithError("CSV file not found for table [{$table}].");
        }

        [$headers, $rows] = $this->readCsv($path);

        if ($headers === []) {
            return $this->reportWithError("CSV file is empty for table [{$table}], skipped.");
        }

        $this->bootstrapImportDependencies($table, $rows);
        [$headers, $rows] = $this->applyColumnAliases($headers, $rows, $config);
        $columnListing = Schema::getColumnListing($table);
        $unexpectedHeaders = array_values(array_diff($headers, array_merge($columnListing, $config['helper_columns'])));

        if ($unexpectedHeaders !== []) {
            return $this->reportWithError('Unexpected headers: '.implode(', ', $unexpectedHeaders));
        }

        $report = [
            'table' => $table,
            'file' => $path,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            if (! $dryRun && $this->shouldRelaxSelfReferences($table)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $foreignKeyChecksDisabled = true;
            }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $normalizedRow = $this->normalizeRow($row, $config);
                    $attributes = $this->extractAttributes($normalizedRow, $columnListing);
                    $attributes = $this->resolveRelationships($table, $normalizedRow, $attributes);
                    $lookup = $this->buildLookup($table, $normalizedRow, $attributes);

                    if ($lookup === []) {
                        throw new RuntimeException('Upsert key could not be built from the CSV row.');
                    }

                    $existing = $this->findExisting($table, $lookup);
                    $payload = $this->preparePayload($attributes, $columnListing, $existing !== null);

                    if (! $dryRun) {
                        if ($existing !== null) {
                            DB::table($table)
                                ->where('id', $existing->id)
                                ->update($payload);
                        } else {
                            DB::table($table)->insert($payload);
                        }
                    }

                    if ($existing !== null) {
                        $report['updated']++;
                    } else {
                        $report['inserted']++;
                    }
                } catch (Throwable $throwable) {
                    $report['skipped']++;
                    $report['errors'][] = "Row {$rowNumber}: {$throwable->getMessage()}";
                }
            }

            if (! $dryRun && $table === 'people') {
                $this->cleanupPeopleRelationReferences();
            }

            if ($foreignKeyChecksDisabled) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                $foreignKeyChecksDisabled = false;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Throwable $throwable) {
            if ($foreignKeyChecksDisabled) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            DB::rollBack();
            throw $throwable;
        }

        Log::info('CSV import completed.', [
            'table' => $table,
            'file' => $path,
            'dry_run' => $dryRun,
            'inserted' => $report['inserted'],
            'updated' => $report['updated'],
            'skipped' => $report['skipped'],
            'errors' => $report['errors'],
        ]);

        return $report;
    }

    public function importAll(bool $dryRun = false): array
    {
        $summary = [
            'dry_run' => $dryRun,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'tables' => [],
        ];

        foreach (array_keys(self::TABLES) as $table) {
            $path = $this->resolvePath($table);

            if ($path === null) {
                $summary['tables'][$table] = [
                    'table' => $table,
                    'file' => null,
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 1,
                    'errors' => [],
                    'note' => 'CSV file not found; table skipped during import:csv --all.',
                ];
                $summary['skipped']++;

                continue;
            }

            $result = $this->import($table, $dryRun);
            $summary['tables'][$table] = $result;
            $summary['inserted'] += $result['inserted'];
            $summary['updated'] += $result['updated'];
            $summary['skipped'] += $result['skipped'];
            $summary['errors'] = array_merge(
                $summary['errors'],
                array_map(fn (string $error) => "{$table}: {$error}", $result['errors'])
            );
        }

        return $summary;
    }

    public static function supportedTables(): array
    {
        return array_keys(self::TABLES);
    }

    private function config(string $table): array
    {
        if (! array_key_exists($table, self::TABLES)) {
            throw new RuntimeException("Unsupported table [{$table}].");
        }

        return self::TABLES[$table];
    }

    private function resolvePath(string $table): ?string
    {
        $paths = app()->environment('production')
            ? [storage_path("app/import/{$table}.csv")]
            : [
                storage_path("app/import/{$table}.csv"),
                database_path("csv/{$table}.csv"),
                public_path("csv/{$table}.csv"),
            ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file [{$path}].");
        }

        $headers = fgetcsv($handle, 0, ',', '"', '\\');

        if ($headers === false) {
            fclose($handle);

            // Empty file — return empty result instead of throwing
            return [[], []];
        }

        $headers = array_map(fn (?string $header) => $this->stripBom(trim((string) $header)), $headers);
        $rows = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            $rows[] = array_combine($headers, array_pad($row, count($headers), null));
        }

        fclose($handle);

        return [$headers, $rows];
    }

    private function normalizeRow(array $row, array $config): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '' || $value === 'NULL' || $value === 'null') {
                $value = null;
            }

            if ($value !== null && in_array($key, $config['boolean_columns'], true)) {
                $value = $this->normalizeBoolean($value);
            }

            if ($value !== null && in_array($key, $config['json_columns'], true) && is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }

            $row[$key] = $value;
        }

        return $row;
    }

    private function extractAttributes(array $row, array $columnListing): array
    {
        // Keep `id` intact — used to preserve original IDs on INSERT for self-referential tables.
        return Arr::only($row, $columnListing);
    }

    private function resolveRelationships(string $table, array $row, array $attributes): array
    {
        return match ($table) {
            'stakeholders' => $this->resolveStakeholders($row, $attributes),
            'people' => $this->resolvePeople($row, $attributes),
            'posts' => $this->resolvePosts($row, $attributes),
            'comments' => $this->resolveComments($row, $attributes),
            'incomes' => $this->resolveIncomes($row, $attributes),
            'expenses' => $this->resolveExpenses($row, $attributes),
            'interactions' => $this->resolveInteractions($row, $attributes),
            'node_connections' => $this->resolveNodeConnections($row, $attributes),
            default => $attributes,
        };
    }

    private function buildLookup(string $table, array $row, array $attributes): array
    {
        if ($table === 'incomes') {
            $id = $attributes['id'] ?? $row['id'] ?? null;

            if ($id !== null && $id !== '') {
                return ['id' => $id];
            }
        }

        $keys = $this->config($table)['upsert'];
        $lookup = [];

        foreach ($keys as $key) {
            $value = $attributes[$key] ?? $row[$key] ?? null;
            if ($value === null) {
                return [];
            }
            $lookup[$key] = $value;
        }

        return $lookup;
    }

    private function findExisting(string $table, array $lookup): ?object
    {
        $query = DB::table($table);

        foreach ($lookup as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->first();
    }

    private function preparePayload(array $attributes, array $columnListing, bool $isUpdate): array
    {
        $payload = Arr::only($attributes, $columnListing);
        $now = now()->toDateTimeString();

        // Never change an existing record's primary key.
        if ($isUpdate) {
            unset($payload['id']);
        } elseif (isset($payload['id']) && ($payload['id'] === null || $payload['id'] === '')) {
            // Discard null/empty id so the DB auto-assigns one.
            unset($payload['id']);
        }

        if (in_array('updated_at', $columnListing, true) && empty($payload['updated_at'])) {
            $payload['updated_at'] = $now;
        }

        if (! $isUpdate && in_array('created_at', $columnListing, true) && empty($payload['created_at'])) {
            $payload['created_at'] = $now;
        }

        return $payload;
    }

    private function resolveStakeholders(array $row, array $attributes): array
    {
        if (($attributes['created_by'] ?? null) === null && ($row['user_email'] ?? null) !== null) {
            $attributes['created_by'] = $this->lookupId('users', 'email', $row['user_email'], 'created_by');
        }

        return $attributes;
    }

    private function resolvePeople(array $row, array $attributes): array
    {
        if (($attributes['surname'] ?? null) === null || trim((string) $attributes['surname']) === '') {
            $fallback = trim((string) ($attributes['second_surname'] ?? $row['second_surname'] ?? ''));

            if ($fallback === '') {
                $name = trim((string) ($attributes['name'] ?? $row['name'] ?? ''));
                $parts = preg_split('/\s+/', $name);
                $fallback = is_array($parts) && count($parts) > 1 ? (string) end($parts) : '';
            }

            $attributes['surname'] = $fallback !== '' ? $fallback : 'Unknown';
        }

        if (($attributes['gender_id'] ?? null) === null) {
            if (($row['gender_name'] ?? null) !== null) {
                $attributes['gender_id'] = $this->lookupId('genders', 'name', $row['gender_name'], 'gender_name');
            } elseif (($row['gender_slug'] ?? null) !== null) {
                $attributes['gender_id'] = $this->lookupId('genders', 'slug', $row['gender_slug'], 'gender_slug');
            }
        }

        if (($attributes['eye_color_id'] ?? null) === null) {
            if (($row['eye_color_name'] ?? null) !== null) {
                $attributes['eye_color_id'] = $this->findOrCreateId('eye_colors', 'name', $row['eye_color_name']);
            } elseif (($row['eye_color_slug'] ?? null) !== null) {
                $attributes['eye_color_id'] = $this->findOrCreateId('eye_colors', 'slug', $row['eye_color_slug']);
            }
        }

        if (($attributes['blood_type_id'] ?? null) === null && ($row['blood_type_name'] ?? null) !== null) {
            $normalizedName = self::BLOOD_TYPE_ALIASES[$row['blood_type_name']] ?? $row['blood_type_name'];
            $attributes['blood_type_id'] = $this->findOrCreateId('blood_types', 'name', $normalizedName);
        }

        if (($attributes['hair_color_id'] ?? null) === null) {
            if (($row['hair_color_name'] ?? null) !== null) {
                $attributes['hair_color_id'] = $this->findOrCreateId('hair_colors', 'name', $row['hair_color_name']);
            } elseif (($row['hair_color_slug'] ?? null) !== null) {
                $attributes['hair_color_id'] = $this->findOrCreateId('hair_colors', 'slug', $row['hair_color_slug']);
            }
        }

        return $attributes;
    }

    private function resolvePosts(array $row, array $attributes): array
    {
        if (($attributes['category_id'] ?? null) === null && ($row['category_slug'] ?? null) !== null) {
            $attributes['category_id'] = $this->lookupId('post_categories', 'slug', $row['category_slug'], 'category_slug');
        }

        if (($attributes['language_id'] ?? null) === null && ($row['language_code'] ?? null) !== null) {
            $attributes['language_id'] = $this->lookupId('post_languages', 'code', $row['language_code'], 'language_code');
        }

        if (($attributes['user_id'] ?? null) === null && ($row['user_email'] ?? null) !== null) {
            $attributes['user_id'] = $this->lookupId('users', 'email', $row['user_email'], 'user_email');
        }

        return $attributes;
    }

    private function resolveComments(array $row, array $attributes): array
    {
        if (($attributes['post_id'] ?? null) === null && ($row['post_slug'] ?? null) !== null) {
            $attributes['post_id'] = $this->lookupId('posts', 'slug', $row['post_slug'], 'post_slug');
        }

        if (($attributes['user_id'] ?? null) === null && ($row['user_email'] ?? null) !== null) {
            $attributes['user_id'] = $this->lookupId('users', 'email', $row['user_email'], 'user_email');
        }

        return $attributes;
    }

    private function resolveIncomes(array $row, array $attributes): array
    {
        if (array_key_exists('amount', $attributes) && $attributes['amount'] !== null) {
            $attributes['amount'] = $this->toDecimal($attributes['amount'], 'amount');
        }

        if (($attributes['income_source_id'] ?? null) === null && ($row['income_source_name'] ?? null) !== null) {
            $attributes['income_source_id'] = $this->findOrCreateId('income_sources', 'name', $row['income_source_name']);
        }

        if (($attributes['income_type_id'] ?? null) === null && ($row['income_type_name'] ?? null) !== null) {
            $attributes['income_type_id'] = $this->findOrCreateId('income_types', 'name', $row['income_type_name']);
        }

        if (($attributes['currency_id'] ?? null) === null && ($row['currency_code'] ?? null) !== null) {
            $attributes['currency_id'] = $this->lookupId('currencies', 'code', Str::upper($row['currency_code']), 'currency_code');
        }

        if (($attributes['currency_id'] ?? null) === null && ($row['currency_symbol'] ?? null) !== null) {
            $attributes['currency_id'] = $this->lookupId('currencies', 'symbol', $row['currency_symbol'], 'currency_symbol');
        }

        if (($attributes['user_id'] ?? null) === null && ($row['user_email'] ?? null) !== null) {
            $attributes['user_id'] = $this->lookupId('users', 'email', $row['user_email'], 'user_email');
        }

        return $attributes;
    }

    private function resolveExpenses(array $row, array $attributes): array
    {
        $price = $this->toDecimal($attributes['price'] ?? ($row['price'] ?? null), 'price') ?? 0.0;
        $quantity = $this->toDecimal($attributes['quantity'] ?? ($row['quantity'] ?? null), 'quantity') ?? 1.0;
        $tax = $this->toDecimal($attributes['tax'] ?? ($row['tax'] ?? null), 'tax') ?? 0.0;

        $attributes['price'] = $price;
        $attributes['quantity'] = $quantity;
        $attributes['tax'] = $tax;

        if (($attributes['stakeholder_id'] ?? null) === null && ($row['stakeholder_vkn'] ?? null) !== null) {
            $attributes['stakeholder_id'] = $this->lookupId('stakeholders', 'vkn_tckn', $row['stakeholder_vkn'], 'stakeholder_vkn');
        }

        if (($attributes['expense_type_id'] ?? null) === null && ($row['expense_type_name'] ?? null) !== null) {
            $attributes['expense_type_id'] = $this->findOrCreateId('expense_types', 'name', $row['expense_type_name']);
        }

        if (($attributes['currency_id'] ?? null) === null && ($row['currency_code'] ?? null) !== null) {
            $attributes['currency_id'] = $this->lookupId('currencies', 'code', Str::upper($row['currency_code']), 'currency_code');
        }

        if (($attributes['currency_id'] ?? null) === null && ($row['currency_symbol'] ?? null) !== null) {
            $attributes['currency_id'] = $this->lookupId('currencies', 'symbol', $row['currency_symbol'], 'currency_symbol');
        }

        if (($attributes['paid_by_others'] ?? null) === null) {
            $attributes['paid_by_others'] = false;
        }

        if (($attributes['company_expense'] ?? null) === null) {
            $attributes['company_expense'] = false;
        }

        $providedTotal = $this->toDecimal($attributes['total'] ?? ($row['total'] ?? null), 'total');
        $attributes['total'] = $providedTotal ?? ($price * $quantity);

        return $attributes;
    }

    private function bootstrapImportDependencies(string $table, array $rows): void
    {
        if ($table !== 'expenses') {
            return;
        }

        $now = now()->toDateTimeString();
        $names = self::DEFAULT_EXPENSE_TYPES;

        foreach ($rows as $row) {
            $legacyType = isset($row['type']) ? trim((string) $row['type']) : '';
            $normalizedType = isset($row['expense_type_name']) ? trim((string) $row['expense_type_name']) : '';

            if ($legacyType !== '') {
                $names[] = $legacyType;
            }

            if ($normalizedType !== '') {
                $names[] = $normalizedType;
            }
        }

        $names = array_values(array_unique(array_filter($names, fn (string $name) => $name !== '')));

        foreach ($names as $name) {
            DB::table('expense_types')->updateOrInsert(
                ['name' => $name],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }
    }

    private function toDecimal(mixed $value, string $label): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d,\.\-]/', '', $raw) ?? '';

        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            throw new RuntimeException("Decimal value [{$value}] for {$label} is not valid.");
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (! is_numeric($normalized)) {
            throw new RuntimeException("Decimal value [{$value}] for {$label} is not valid.");
        }

        return (float) $normalized;
    }

    private function resolveInteractions(array $row, array $attributes): array
    {
        if (($attributes['person_id'] ?? null) === null) {
            $personName = $row['person_name'] ?? null;
            $personSurname = $row['person_surname'] ?? null;
            $personBirthday = $row['person_birthday'] ?? null;

            if ($personName !== null || $personSurname !== null || $personBirthday !== null) {
                if ($personName === null || $personSurname === null || $personBirthday === null) {
                    throw new RuntimeException('person_name, person_surname, and person_birthday are all required to resolve person_id.');
                }

                $attributes['person_id'] = DB::table('people')
                    ->where('name', $personName)
                    ->where('surname', $personSurname)
                    ->where('birthday', $personBirthday)
                    ->value('id');

                if ($attributes['person_id'] === null) {
                    throw new RuntimeException('Unable to resolve person_id from person_name/person_surname/person_birthday.');
                }
            }
        }

        if (($attributes['interaction_type_id'] ?? null) !== null) {
            $attributes['interaction_type_id'] = $this->ensureInteractionTypeId($attributes['interaction_type_id']);
        }

        if (($attributes['interaction_type_id'] ?? null) === null && ($row['interaction_type_name'] ?? null) !== null) {
            $attributes['interaction_type_id'] = $this->lookupId('interaction_types', 'name', $row['interaction_type_name'], 'interaction_type_name');
        }

        return $attributes;
    }

    private function ensureInteractionTypeId(mixed $rawId): int
    {
        $legacyId = (int) $rawId;

        if ($legacyId <= 0) {
            return $this->findOrCreateId('interaction_types', 'name', 'Diger');
        }

        $existing = DB::table('interaction_types')->where('id', $legacyId)->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        $legacyName = "Legacy Type {$legacyId}";
        $byName = DB::table('interaction_types')->where('name', $legacyName)->value('id');

        if ($byName !== null) {
            return (int) $byName;
        }

        DB::table('interaction_types')->insert([
            'id' => $legacyId,
            'name' => $legacyName,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return $legacyId;
    }

    private function resolveNodeConnections(array $row, array $attributes): array
    {
        if (($attributes['node_from_id'] ?? null) === null && ($row['node_from'] ?? null) !== null) {
            $attributes['node_from_id'] = $this->lookupId('nodes', 'name', $row['node_from'], 'node_from');
        }

        if (($attributes['node_to_id'] ?? null) === null && ($row['node_to'] ?? null) !== null) {
            $attributes['node_to_id'] = $this->lookupId('nodes', 'name', $row['node_to'], 'node_to');
        }

        return $attributes;
    }

    private function resolveNullableLookup(mixed $currentValue, array $candidates): mixed
    {
        if ($currentValue !== null) {
            return $currentValue;
        }

        foreach ($candidates as $candidate) {
            if ($candidate['value'] === null) {
                continue;
            }

            return $this->lookupId($candidate['table'], $candidate['column'], $candidate['value'], $candidate['label']);
        }

        return null;
    }

    private function lookupId(string $table, string $column, mixed $value, string $label): mixed
    {
        $id = DB::table($table)->where($column, $value)->value('id');

        if ($id === null) {
            throw new RuntimeException("Unable to resolve {$label} [{$value}] in {$table}.{$column}.");
        }

        return $id;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($normalized === null) {
            throw new RuntimeException("Boolean value [{$value}] is not valid.");
        }

        return $normalized;
    }

    private function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function reportWithError(string $message): array
    {
        return [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [$message],
        ];
    }

    private function shouldRelaxSelfReferences(string $table): bool
    {
        return $table === 'people';
    }

    private function cleanupPeopleRelationReferences(): void
    {
        DB::statement('UPDATE people p LEFT JOIN people f ON p.father_id = f.id SET p.father_id = NULL WHERE p.father_id IS NOT NULL AND f.id IS NULL');
        DB::statement('UPDATE people p LEFT JOIN people m ON p.mother_id = m.id SET p.mother_id = NULL WHERE p.mother_id IS NOT NULL AND m.id IS NULL');
        DB::statement('UPDATE people p LEFT JOIN people pr ON p.partner_id = pr.id SET p.partner_id = NULL WHERE p.partner_id IS NOT NULL AND pr.id IS NULL');
    }

    private function findOrCreateId(string $table, string $column, mixed $value): int
    {
        $id = DB::table($table)->where($column, $value)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $payload = [
            $column => $value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($column !== 'slug' && in_array('slug', Schema::getColumnListing($table), true)) {
            $payload['slug'] = Str::slug((string) $value);
        }

        return (int) DB::table($table)->insertGetId($payload);
    }

    private function applyColumnAliases(array $headers, array $rows, array $config): array
    {
        $aliases = $config['column_aliases'] ?? [];
        $ignore = $config['ignore_columns'] ?? [];

        if ($aliases === [] && $ignore === []) {
            return [$headers, $rows];
        }

        $newHeaders = array_values(array_filter(
            array_map(fn (string $header) => $aliases[$header] ?? $header, $headers),
            fn (string $header) => ! in_array($header, $ignore, true),
        ));

        $rows = array_map(function (array $row) use ($aliases, $ignore) {
            $newRow = [];

            foreach ($row as $key => $value) {
                if (in_array($key, $ignore, true)) {
                    continue;
                }

                $newRow[$aliases[$key] ?? $key] = $value;
            }

            return $newRow;
        }, $rows);

        return [$newHeaders, $rows];
    }
}
