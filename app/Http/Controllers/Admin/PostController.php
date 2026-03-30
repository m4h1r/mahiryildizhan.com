<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostLanguage;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::query()
            ->with(['category', 'language', 'author', 'coverMedia'])
            ->latest('published_at')
            ->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($languageId = $request->integer('language_id')) {
            $query->where('language_id', $languageId);
        }

        if ($request->has('published') && $request->input('published') !== '') {
            $query->where('published', $request->boolean('published'));
        }

        return view('admin.posts.index', [
            'title' => 'Posts',
            'heading' => 'Posts',
            'posts' => $query->paginate(20)->withQueryString(),
            'categories' => PostCategory::query()->orderBy('name')->get(),
            'languages' => PostLanguage::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'category_id', 'language_id', 'published']),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'title' => 'New Post',
            'heading' => 'New Post',
            'categories' => PostCategory::query()->orderBy('name')->get(),
            'languages' => PostLanguage::query()->orderBy('name')->get(),
            'mediaItems' => Media::query()->latest('id')->limit(100)->get(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $payload = $this->buildPayload($request->validated(), null, $request);
        $payload['user_id'] = $request->user()?->id;

        Post::query()->create($payload);

        return to_route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'title' => 'Edit Post',
            'heading' => 'Edit Post',
            'post' => $post,
            'categories' => PostCategory::query()->orderBy('name')->get(),
            'languages' => PostLanguage::query()->orderBy('name')->get(),
            'mediaItems' => Media::query()->latest('id')->limit(100)->get(),
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $payload = $this->buildPayload($request->validated(), $post, $request);

        $post->update($payload);

        return to_route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function uploadImage(Request $request, MediaService $mediaService): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:6144'],
        ]);

        $media = $mediaService->upload($validated['image'], 'media/editor');

        return response()->json([
            'url' => $media->url('webp'),
            'path' => $media->webp_path ?: $media->path,
            'media_id' => $media->id,
        ]);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return to_route('admin.posts.index')->with('success', 'Post deleted.');
    }

    private function buildPayload(array $payload, ?Post $post, Request $request): array
    {
        unset($payload['cover_upload']);

        if ($request->hasFile('cover_upload')) {
            $coverMedia = app(MediaService::class)->upload($request->file('cover_upload'), 'media/covers');
            $payload['cover_media_id'] = $coverMedia->id;
            $payload['cover'] = null;
        } elseif (! empty($payload['cover_media_id'])) {
            $payload['cover'] = null;
        }

        $payload['slug'] = $this->uniqueSlug(
            $payload['slug'] ?? null,
            $payload['title'],
            $post?->id
        );

        $plainTextBody = trim(strip_tags((string) $payload['body']));
        $wordCount = max(1, str_word_count($plainTextBody));

        $payload['word_count'] = $wordCount;
        $payload['reading_time'] = (int) max(1, ceil($wordCount / 200));

        if (empty($payload['excerpt'])) {
            $payload['excerpt'] = Str::of($plainTextBody)->limit(180, '...')->toString();
        }

        $status = $payload['status'];
        $publishedFlag = (bool) ($payload['published'] ?? false);

        if ($status === 'published' || $publishedFlag) {
            $payload['status'] = 'published';
            $payload['published'] = true;
            $payload['published_at'] = $post?->published_at ?? Carbon::now();
        } else {
            $payload['published'] = false;
            $payload['published_at'] = null;
        }

        return $payload;
    }

    private function uniqueSlug(?string $slugInput, string $title, ?int $ignorePostId = null): string
    {
        $baseSlug = Str::slug((string) ($slugInput ?: $title));

        if ($baseSlug === '') {
            $baseSlug = 'post';
        }

        $slug = $baseSlug;
        $attempt = 1;

        while (
            Post::query()
                ->when($ignorePostId !== null, fn ($query) => $query->where('id', '!=', $ignorePostId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$attempt;
            $attempt++;
        }

        return $slug;
    }
}
