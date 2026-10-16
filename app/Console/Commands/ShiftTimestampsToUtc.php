<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time Europe/Istanbul -> UTC timestamp migration (L16).
 *
 * The app historically ran with APP_TIMEZONE=Europe/Istanbul, so every stored
 * datetime is Istanbul wall-clock. The standard mandates STORE-UTC / DISPLAY-
 * Istanbul. This command shifts existing datetime/timestamp columns by the
 * fixed +03:00 offset (Turkey has had no DST since 2016) so the data matches a
 * UTC APP_TIMEZONE.
 *
 * SAFETY:
 *   - Dry-run by default; pass --execute to write.
 *   - Reversible: --reverse shifts the other way (UTC -> Istanbul).
 *   - Skips date-only columns (a birthday has no timezone) and framework/metric
 *     tables (sessions, cache, jobs, pulse_*, telescope_*, migrations).
 *   - RUN IT ONLY once, against a fresh backup, in a maintenance window, in the
 *     SAME deploy that sets APP_TIMEZONE=UTC — never unattended.
 */
class ShiftTimestampsToUtc extends Command
{
    protected $signature = 'app:shift-timestamps-to-utc {--execute : Actually write (otherwise dry-run)} {--reverse : Shift UTC->Istanbul instead}';

    protected $description = 'Shift stored timestamps between Europe/Istanbul and UTC (L16)';

    private const OFFSET_HOURS = 3; // Istanbul = UTC+3, no DST since 2016

    private const SKIP_TABLES = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
        'failed_jobs', 'password_reset_tokens', 'personal_access_tokens',
    ];

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            $this->error("Unsupported driver [{$driver}].");

            return self::FAILURE;
        }

        $reverse = (bool) $this->option('reverse');
        $execute = (bool) $this->option('execute');
        $sign = $reverse ? +self::OFFSET_HOURS : -self::OFFSET_HOURS; // Istanbul->UTC subtracts 3h
        $direction = $reverse ? 'UTC -> Europe/Istanbul (+3h)' : 'Europe/Istanbul -> UTC (-3h)';

        $this->info(($execute ? 'EXECUTING' : 'DRY RUN').' — '.$direction);

        $total = 0;

        foreach (Schema::getTableListing() as $table) {
            $bare = str_contains($table, '.') ? substr($table, (int) strrpos($table, '.') + 1) : $table;
            if (in_array($bare, self::SKIP_TABLES, true) || str_starts_with($bare, 'pulse_') || str_starts_with($bare, 'telescope_')) {
                continue;
            }

            foreach (Schema::getColumns($table) as $column) {
                $type = strtolower($column['type_name'] ?? '');
                if (! in_array($type, ['datetime', 'timestamp'], true)) {
                    continue; // never shift date-only / time-only / numeric columns
                }

                $col = $column['name'];
                $count = DB::table($table)->whereNotNull($col)->count();
                if ($count === 0) {
                    continue;
                }

                $total += $count;
                $this->line(sprintf('  %s.%s — %d row(s)', $bare, $col, $count));

                if (! $execute) {
                    continue;
                }

                if ($driver === 'sqlite') {
                    DB::statement("UPDATE \"{$table}\" SET \"{$col}\" = datetime(\"{$col}\", ? ) WHERE \"{$col}\" IS NOT NULL", [sprintf('%+d hours', $sign)]);
                } else {
                    $fn = $sign < 0 ? 'DATE_SUB' : 'DATE_ADD';
                    DB::statement("UPDATE `{$table}` SET `{$col}` = {$fn}(`{$col}`, INTERVAL ".self::OFFSET_HOURS." HOUR) WHERE `{$col}` IS NOT NULL");
                }
            }
        }

        $this->newLine();
        $this->info(($execute ? 'Shifted ' : 'Would shift ')."{$total} value(s).");
        if (! $execute) {
            $this->warn('Dry run only. Re-run with --execute (against a backup, in a maintenance window) to apply.');
        }

        return self::SUCCESS;
    }
}
