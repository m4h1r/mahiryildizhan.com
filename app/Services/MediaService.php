<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaService
{
    private const VARIANTS = [
        'sm' => 320,
        'md' => 640,
        'lg' => 1200,
    ];

    public function upload(UploadedFile $file, string $directory = 'media'): Media
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(trim($directory, '/'), $filename, 'public');
        $fullPath = Storage::disk('public')->path($path);

        $media = Media::query()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'extension' => strtolower((string) $file->getClientOriginalExtension()),
            'checksum' => is_file($fullPath) ? hash_file('sha256', $fullPath) : null,
            'size' => $file->getSize() ?: 0,
            'type' => str_starts_with((string) $file->getMimeType(), 'image/') ? 1 : 2,
        ]);

        if ($media->type === 1) {
            $this->processImage($media);
        }

        return $media;
    }

    public function processImage(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $fullPath = $disk->path($media->path);

        if (! is_file($fullPath)) {
            return;
        }

        // Skip GIF processing — animated GIFs exhaust memory during frame splitting
        if (in_array($media->mime_type, ['image/gif'], true)) {
            $size = getimagesize($fullPath);
            if ($size) {
                $media->width = $size[0];
                $media->height = $size[1];
                $media->save();
            }
            return;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($fullPath);

        $media->width = $image->width();
        $media->height = $image->height();

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $media->path) ?: $media->path;
        $image->toWebp(85)->save($disk->path($webpPath));
        $media->webp_path = $webpPath;

        $variantPaths = [];
        foreach (self::VARIANTS as $key => $targetWidth) {
            if ($media->width !== null && $media->width <= $targetWidth) {
                continue;
            }

            $variantPath = preg_replace('/^media\//', "media/{$key}/", $media->path, 1) ?: "media/{$key}/".basename($media->path);
            $variantPath = preg_replace('/\.[^.]+$/', '.webp', $variantPath) ?: $variantPath;
            $absoluteVariantPath = $disk->path($variantPath);

            if (! is_dir(dirname($absoluteVariantPath))) {
                mkdir(dirname($absoluteVariantPath), 0755, true);
            }

            $variant = $manager->read($fullPath);
            $variant->scaleDown(width: $targetWidth)->toWebp($key === 'sm' ? 75 : 80)->save($absoluteVariantPath);

            $variantPaths[$key] = $variantPath;
        }

        $thumbPath = str_replace('media/', 'media/thumbs/', $media->path);
        $thumbWebpPath = preg_replace('/\.[^.]+$/', '.webp', $thumbPath) ?: $thumbPath;
        $thumbDiskPath = $disk->path($thumbWebpPath);

        if (! is_dir(dirname($thumbDiskPath))) {
            mkdir(dirname($thumbDiskPath), 0755, true);
        }

        $thumb = $manager->read($fullPath);
        $thumb->scaleDown(width: 320)->toWebp(80)->save($thumbDiskPath);
        $media->thumbnail_path = $thumbWebpPath;
        $media->variant_paths = $variantPaths;

        $media->save();
    }

    public function deleteFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        $paths = [$media->path, $media->webp_path, $media->thumbnail_path];
        foreach (($media->variant_paths ?? []) as $variantPath) {
            $paths[] = $variantPath;
        }

        foreach (array_filter($paths) as $path) {
            $disk->delete($path);
        }
    }

    public function importFromPublicPath(string $path, ?string $originalName = null): ?Media
    {
        $normalizedPath = ltrim($path, '/');
        $disk = Storage::disk('public');

        if (! $disk->exists($normalizedPath)) {
            return null;
        }

        $existing = Media::query()->where('disk', 'public')->where('path', $normalizedPath)->first();
        if ($existing) {
            return $existing;
        }

        $absolutePath = $disk->path($normalizedPath);
        $mimeType = $disk->mimeType($normalizedPath) ?: 'application/octet-stream';
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));

        $media = Media::query()->create([
            'filename' => $originalName ?: basename($normalizedPath),
            'path' => $normalizedPath,
            'disk' => 'public',
            'mime_type' => $mimeType,
            'extension' => $extension,
            'checksum' => is_file($absolutePath) ? hash_file('sha256', $absolutePath) : null,
            'size' => (int) ($disk->size($normalizedPath) ?? 0),
            'type' => str_starts_with($mimeType, 'image/') ? 1 : 2,
        ]);

        if ($media->type === 1) {
            $this->processImage($media);
        }

        return $media;
    }
}
