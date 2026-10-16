<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Generates /llms.txt per locale (E9). This is NOT a settings field and is
 * never hand-edited — it is regenerated from live content so it cannot go
 * stale and point agents at 404s. It is not an SEO lever.
 */
class GenerateLlmsTxt extends Command
{
    protected $signature = 'llms:generate';

    protected $description = 'Generate the per-locale llms.txt files (E9)';

    /** @var array<string,string> */
    private array $locales = ['tr' => 'tr', 'en' => 'en'];

    public function handle(): int
    {
        foreach ($this->locales as $locale) {
            Storage::disk('local')->put(
                "llms/{$locale}.txt",
                $this->build($locale)
            );
            $this->info("Generated llms/{$locale}.txt");
        }

        return self::SUCCESS;
    }

    private function build(string $locale): string
    {
        $siteName = Setting::get('site_name', config('app.name'));
        $base = rtrim(config('app.url'), '/');

        $tagline = $locale === 'en'
            ? 'Personal blog and writing of Mahir Yıldızhan.'
            : 'Mahir Yıldızhan’ın kişisel blogu ve yazıları.';

        $lines = [
            '# '.$siteName,
            '',
            '> '.$tagline,
            '',
            $locale === 'en' ? '## Key pages' : '## Önemli sayfalar',
            '- ['.($locale === 'en' ? 'Home' : 'Ana sayfa').']('.$base.'/)',
            '- ['.($locale === 'en' ? 'Stories' : 'Yazılar').']('.$base.'/blog)',
            '- ['.($locale === 'en' ? 'About' : 'Hakkında').']('.$base.'/about)',
            '',
            $locale === 'en' ? '## Recent posts' : '## Son yazılar',
        ];

        Post::query()
            ->publiclyVisible()
            ->latest('published_at')
            ->limit(25)
            ->get()
            ->each(function (Post $post) use (&$lines, $base) {
                $title = trim((string) ($post->title ?? ''));
                if ($title === '' || ! $post->slug) {
                    return;
                }
                $lines[] = '- ['.$title.']('.$base.'/blog/'.$post->slug.')';
            });

        return implode("\n", $lines)."\n";
    }
}
