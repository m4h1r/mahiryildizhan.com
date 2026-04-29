<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::query()
            ->with(['category', 'language', 'author', 'coverMedia'])
            ->publiclyVisible()
            ->orderByRaw('COALESCE(published_at, publish_date) DESC')
            ->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('public.blog.index', [
            'title' => 'Blog | '.config('app.name'),
            'description' => 'Long-form notes, ideas, and updates presented in a calm reading-focused layout.',
            'posts' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $post = Post::query()
            ->with([
                'category',
                'language',
                'author',
                'coverMedia',
                'comments' => fn ($query) => $query
                    ->where('is_approved', true)
                    ->with('user')
                    ->latest('id'),
            ])
            ->where('slug', $slug)
            ->publiclyVisible()
            ->firstOrFail();

        $post->increment('view_count');

        $uniqueViewKey = 'post_unique_viewed_'.$post->id;

        if (! $request->session()->has($uniqueViewKey)) {
            $post->increment('unique_view_count');
            $request->session()->put($uniqueViewKey, true);
        }

        $post->refresh();

        return view('public.blog.show', [
            'title' => $post->seo_title ?: $post->title,
            'description' => $post->seo_description ?: $post->excerpt,
            'seoPost' => $post,
            'post' => $post,
        ]);
    }

    public function trackView(Request $request, string $slug): JsonResponse
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->publiclyVisible()
            ->firstOrFail();

        $post->increment('view_count');

        $uniqueKey = sprintf(
            'post_unique_view:%s:%s:%s',
            $post->id,
            hash('sha256', (string) $request->ip()),
            now()->format('Y-m-d')
        );

        if (Cache::add($uniqueKey, true, now()->addHours(24))) {
            $post->increment('unique_view_count');
        }

        $post->refresh();

        return response()->json([
            'view_count' => $post->view_count,
            'unique_view_count' => $post->unique_view_count,
        ]);
    }
}
