<?php

use App\Jobs\GenerateSitemapJob;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Person;
use App\Models\Post;
use App\Services\CsvImportService;
use App\Services\MediaService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:csv {table?} {--all} {--dry-run}', function () {
    $service = app(CsvImportService::class);
    $table = $this->argument('table');
    $dryRun = (bool) $this->option('dry-run');
    $importAll = (bool) $this->option('all');

    if (! $importAll && $table === null) {
        $this->error('Provide a table name or use --all.');

        return self::FAILURE;
    }

    if ($importAll && $table !== null) {
        $this->warn('The table argument is ignored when --all is used.');
    }

    $result = $importAll
        ? $service->importAll($dryRun)
        : $service->import($table, $dryRun);

    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
})->purpose('Import one or more CSV files into the application tables.');

Artisan::command('media:migrate-covers {--dry-run}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $mediaService = app(MediaService::class);

    $posts = Post::query()
        ->whereNull('cover_media_id')
        ->whereNotNull('cover')
        ->where('cover', '!=', '')
        ->get();

    if ($posts->isEmpty()) {
        $this->info('No legacy cover records found.');

        return self::SUCCESS;
    }

    $linked = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($posts as $post) {
        $cover = trim((string) $post->cover);

        if ($cover === '' || str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            $skipped++;
            continue;
        }

        $relativePath = str_starts_with($cover, 'covers/') || str_starts_with($cover, 'media/')
            ? $cover
            : 'covers/'.$cover;

        if ($dryRun) {
            $this->line("[DRY] Post #{$post->id} -> {$relativePath}");
            $linked++;
            continue;
        }

        try {
            $media = $mediaService->importFromPublicPath($relativePath, basename($relativePath));

            if (! $media instanceof Media) {
                $skipped++;
                continue;
            }

            $post->cover_media_id = $media->id;
            $post->cover = null;
            $post->save();

            $linked++;
        } catch (Throwable $e) {
            $failed++;
            $this->error("Failed for post #{$post->id}: {$e->getMessage()}");
        }
    }

    $this->line(json_encode([
        'dry_run' => $dryRun,
        'total_candidates' => $posts->count(),
        'linked' => $linked,
        'skipped' => $skipped,
        'failed' => $failed,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Migrate legacy posts.cover file paths into media records and assign cover_media_id.');

Schedule::job(new GenerateSitemapJob())->daily();
Schedule::command('model:prune', ['--model' => [Post::class, Comment::class, Person::class]])->weekly();
