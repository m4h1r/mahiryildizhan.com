<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PruneRetentionData extends Command
{
    protected $signature = 'data:prune {--dry-run : Report what would be pruned without deleting}';

    protected $description = 'Enforce the KVKK data-retention policy (S17) declared in config/retention.php';

    public function handle(): int
    {
        $policies = config('retention.policies', []);
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach ($policies as $name => $policy) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $policy['model'];
            $column = $policy['timestamp'];
            $cutoff = now()->subDays((int) $policy['days']);

            $query = $modelClass::query();

            if (! empty($policy['only_trashed']) && $this->usesSoftDeletes($modelClass)) {
                $query->onlyTrashed();
            }

            foreach ($policy['where'] ?? [] as $col => $value) {
                $query->where($col, $value);
            }

            $query->whereNotNull($column)->where($column, '<', $cutoff);

            $count = (clone $query)->count();

            if ($count === 0) {
                $this->line("  [{$name}] nothing to prune");

                continue;
            }

            if ($dryRun) {
                $this->warn("  [{$name}] would prune {$count} row(s) older than {$cutoff->toDateString()}");
                $total += $count;

                continue;
            }

            if (! empty($policy['force']) && $this->usesSoftDeletes($modelClass)) {
                $query->forceDelete();
            } else {
                $query->delete();
            }

            $this->info("  [{$name}] pruned {$count} row(s)");
            $total += $count;
        }

        $this->info(($dryRun ? 'Dry run complete. ' : 'Done. ')."Total: {$total} row(s).");

        return self::SUCCESS;
    }

    private function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}
