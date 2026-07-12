<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    public function optimize(UploadedFile $file, int $maxW, int $maxH, string $directory): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());
        $image->scaleDown($maxW, $maxH);

        $filename = Str::uuid()->toString().'.webp';
        $directoryPath = storage_path('app/public/'.trim($directory, '/'));

        File::ensureDirectoryExists($directoryPath);
        $image->toWebp(85)->save($directoryPath.'/'.$filename);

        return trim($directory, '/').'/'.$filename;
    }
}
