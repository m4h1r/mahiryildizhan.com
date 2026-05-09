<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Media::query()->withCount('coverPosts')->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('filename', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        if ($type = $request->integer('type')) {
            $query->where('type', $type);
        }

        return view('admin.media.index', [
            'title' => 'Media',
            'heading' => 'Media',
            'mediaItems' => $query->paginate(24)->withQueryString(),
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function store(Request $request, MediaService $mediaService): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $media = $mediaService->upload($validated['file']);
        $media->update([
            'alt' => $validated['alt'] ?? null,
            'caption' => $validated['caption'] ?? null,
        ]);

        return to_route('admin.media.index')->with('success', 'Media uploaded.');
    }

    public function uploadJson(Request $request, MediaService $mediaService): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:6144', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);

        $media = $mediaService->upload($validated['file'], 'media/uploads');

        return response()->json([
            'url' => $media->url('webp'),
            'thumbnail_url' => $media->url('thumbnail'),
            'path' => $media->webp_path ?: $media->path,
            'filename' => $media->filename,
            'media_id' => $media->id,
        ]);
    }

    public function destroy(Media $media, MediaService $mediaService): RedirectResponse
    {
        if ($media->coverPosts()->exists()) {
            return to_route('admin.media.index')->with('error', 'Media is used as cover in one or more posts.');
        }

        $mediaService->deleteFiles($media);
        $media->delete();

        return to_route('admin.media.index')->with('success', 'Media deleted.');
    }

    public function library(Request $request): JsonResponse
    {
        $query = Media::query()->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('filename', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        if ($type = $request->integer('type')) {
            $query->where('type', $type);
        }

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(60, max(12, (int) $request->integer('per_page', 24)));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $items = $paginator->items();

        return response()->json([
            'data' => collect($items)->map(fn (Media $media) => [
                'id' => $media->id,
                'filename' => $media->filename,
                'alt' => $media->alt,
                'caption' => $media->caption,
                'type' => $media->type,
                'mime_type' => $media->mime_type,
                'width' => $media->width,
                'height' => $media->height,
                'thumbnail_url' => $media->url('thumbnail'),
                'url' => $media->url('webp'),
            ])->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }
}
