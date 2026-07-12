<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        $link = Link::query()->where('slug', $slug)->firstOrFail();

        if ($link->expires_at && now()->greaterThan($link->expires_at)) {
            abort(410, 'This file link has expired.');
        }

        $link->increment('download_count');

        return redirect()->to($link->file_path);
    }

    public function biolink(): View
    {
        $links = Link::query()->orderByDesc('id')->get();

        $posts = Post::query()
            ->with('coverMedia')
            ->publiclyVisible()
            ->latest('published_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $socialSettings = Setting::query()
            ->whereIn('key', ['social_tagline', 'social_github', 'social_youtube', 'social_linkedin', 'social_instagram', 'social_twitter'])
            ->pluck('value', 'key');

        return view('public.biolink.index', [
            'title' => 'Biolink | '.config('app.name'),
            'links' => $links,
            'posts' => $posts,
            'social' => $socialSettings,
        ]);
    }
}
