<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StorePostRequest;
use App\Http\Requests\Alice\UpdatePostRequest;
use App\Http\Resources\Alice\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'language'])
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($sub) => $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%"));
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('language_id'), fn ($q, $id) => $q->where('language_id', $id))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applyDateRange($query, $request, 'published_at');
        $this->applySort($query, $request, ['title', 'published_at', 'view_count', 'created_at']);

        return $this->paginate($query, $request, PostResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $post = Post::with(['category', 'language'])->find($id);

        return $post ? $this->success(new PostResource($post)) : $this->notFound();
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']).'-'.uniqid();
        }

        $post = Post::create($data);
        $post->load(['category', 'language']);

        return $this->success(new PostResource($post), 201);
    }

    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (! $post) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $post);
        $post->update($request->validated());
        $post->load(['category', 'language']);

        return $this->success(new PostResource($post));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (! $post) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $post);
        $post->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
