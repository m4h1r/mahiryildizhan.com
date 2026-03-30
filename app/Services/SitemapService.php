<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class SitemapService
{
    public function generate(): string
    {
        $items = [
            [
                'loc' => route('home'),
                'lastmod' => now(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => now(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        $posts = Post::query()
            ->publiclyVisible()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get(['slug', 'updated_at', 'published_at']);

        foreach ($posts as $post) {
            $items[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at ?: $post->published_at ?: now(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        $xml = $this->toXml($items);

        File::put(public_path('sitemap.xml'), $xml);

        return $xml;
    }

    private function toXml(array $items): string
    {
        $entries = collect($items)
            ->map(function (array $item): string {
                $lastmod = $item['lastmod'] instanceof Carbon
                    ? $item['lastmod']->toAtomString()
                    : Carbon::parse($item['lastmod'])->toAtomString();

                return implode('', [
                    '<url>',
                    '<loc>'.e($item['loc']).'</loc>',
                    '<lastmod>'.$lastmod.'</lastmod>',
                    '<changefreq>'.$item['changefreq'].'</changefreq>',
                    '<priority>'.$item['priority'].'</priority>',
                    '</url>',
                ]);
            })
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$entries
            .'</urlset>';
    }
}