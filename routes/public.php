<?php

use App\Http\Controllers\PublicSite\BlogController;
use App\Http\Controllers\PublicSite\CommentController;
use App\Http\Controllers\PublicSite\LinkController;
use App\Http\Controllers\PublicSite\SearchController;
use App\Http\Controllers\PublicSite\SitemapController;
use App\Http\Controllers\PublicSite\SubscriberController;
use App\Http\Controllers\PublicSite\TimelineController;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $latestPost = Post::query()
        ->with(['category', 'coverMedia'])
        ->publiclyVisible()
        ->latest('published_at')
        ->latest('id')
        ->first();

    return view('public.welcome', [
        'latestPost' => $latestPost,
        'featuredPosts' => Post::query()
            ->with(['category', 'coverMedia'])
            ->publiclyVisible()
            ->when($latestPost, fn ($q) => $q->where('id', '!=', $latestPost->id))
            ->latest('published_at')
            ->latest('id')
            ->limit(3)
            ->get(),
    ]);
})->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/timeline', [TimelineController::class, 'publicTimeline'])->name('timeline.public');
Route::get('/biolink', [LinkController::class, 'biolink'])->name('biolink');
Route::view('/about', 'public.about')->name('about');
Route::get('/search', [SearchController::class, 'search'])->name('search');

// Legal pages (E11) — both locales via session locale. Content must be
// reviewed/finalised by the site owner or their lawyer.
Route::view('/privacy-policy', 'public.legal.privacy')->name('legal.privacy');
Route::view('/kvkk', 'public.legal.kvkk')->name('legal.kvkk');
Route::view('/cookie-policy', 'public.legal.cookies')->name('legal.cookies');
Route::view('/terms', 'public.legal.terms')->name('legal.terms');
Route::get('/files/{slug}', [LinkController::class, 'show'])->name('links.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap.xml');

Route::get('/robots.txt', function () {
    // E2: admin/auth paths are NOT listed here. A Disallow stops Googlebot from
    // reading the noindex header (so the URL can stay indexed if linked) and
    // publicly advertises the paths. They are protected by auth + the
    // X-Robots-Tag: noindex header (IndexingPolicy / NoIndex), not by robots.txt.
    //
    // E2.1 default — allow citation, block training: AI search bots that cite
    // sources are allowed; bots that scrape for model training are blocked.
    $lines = [
        'User-agent: *',
        'Allow: /',
        '',
        '# AI training crawlers — blocked (E2.1)',
        'User-agent: GPTBot',
        'Disallow: /',
        '',
        'User-agent: ClaudeBot',
        'Disallow: /',
        '',
        'User-agent: Google-Extended',
        'Disallow: /',
        '',
        'User-agent: CCBot',
        'Disallow: /',
        '',
        '# AI search crawlers (citation) — allowed (E2.1)',
        'User-agent: OAI-SearchBot',
        'Allow: /',
        '',
        'User-agent: Claude-SearchBot',
        'Allow: /',
        '',
        'User-agent: PerplexityBot',
        'Allow: /',
        '',
        'Sitemap: '.route('sitemap.xml'),
    ];

    return response(implode("\n", $lines)."\n", 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots.txt');

Route::get('/ads.txt', function () {
    $clientId = config('services.adsense.client_id')
        ?: Setting::get('adsense_client_id');

    if (! $clientId) {
        abort(404);
    }

    $pubId = preg_replace('/^ca-/', '', $clientId);

    return response("google.com, {$pubId}, DIRECT, f08c47fec0942fa0\n", 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('ads.txt');
Route::post('/comments', [CommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.comments.store');
Route::post('/subscribe', [SubscriberController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('public.subscribers.store');
Route::get('/subscribe/confirm/{subscriber}', [SubscriberController::class, 'confirm'])->name('public.subscribers.confirm');

$serveLlms = function (string $locale) {
    $path = "llms/{$locale}.txt";

    if (! Storage::disk('local')->exists($path)) {
        Artisan::call('llms:generate');
    }

    abort_unless(Storage::disk('local')->exists($path), 404);

    return response(Storage::disk('local')->get($path), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
};

Route::get('/llms.txt', fn () => $serveLlms('tr'))->name('llms.txt');
Route::get('/en/llms.txt', fn () => $serveLlms('en'))->name('llms.txt.en');

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (! in_array($locale, ['tr', 'en'], true)) {
        abort(404);
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');
