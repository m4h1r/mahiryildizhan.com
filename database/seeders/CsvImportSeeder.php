<?php

namespace Database\Seeders;

use App\Services\CsvImportService;
use Illuminate\Database\Seeder;
use RuntimeException;

class CsvImportSeeder extends Seeder
{
    public function run(): void
    {
        $report = app(CsvImportService::class)->importAll(false);
        $errors = $report['errors'] ?? [];
        $missingFileErrors = array_values(array_filter(
            $errors,
            static fn (string $error): bool => str_contains($error, 'CSV file not found for table')
        ));
        $blockingErrors = array_values(array_filter(
            $errors,
            static fn (string $error): bool => ! str_contains($error, 'CSV file not found for table')
        ));

        if ($this->command) {
            $this->command->info('CSV import seeder summary:');
            $this->command->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            if ($missingFileErrors !== []) {
                $this->command->warn('CSV import seeder skipped missing files:');
                foreach ($missingFileErrors as $warning) {
                    $this->command->line('- '.$warning);
                }
            }
        }

        if ($blockingErrors !== []) {
            throw new RuntimeException('CSV import seeder completed with blocking errors: '.implode(' | ', $blockingErrors));
        }
    }
}
