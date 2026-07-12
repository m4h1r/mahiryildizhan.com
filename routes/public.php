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
use Illuminate\Support\Facades\Route;

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
Route::get('/files/{slug}', [LinkController::class, 'show'])->name('links.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap.xml');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /forgot-password',
        'Disallow: /reset-password',
        'Disallow: /profile',
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
Route::post('/comments', [CommentController::class, 'store'])->name('public.comments.store');
Route::post('/subscribe', [SubscriberController::class, 'store'])->name('public.subscribers.store');
Route::get('/subscribe/confirm/{subscriber}', [SubscriberController::class, 'confirm'])->name('public.subscribers.confirm');

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (! in_array($locale, ['tr', 'en'], true)) {
        abort(404);
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');
