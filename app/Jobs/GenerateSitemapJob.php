<?php

namespace App\Jobs;

use App\Services\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSitemapJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Regeneration is idempotent; never run two at once (L15).
    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return 'generate-sitemap';
    }

    public function handle(SitemapService $sitemapService): void
    {
        $sitemapService->generate();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateSitemapJob failed.', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
