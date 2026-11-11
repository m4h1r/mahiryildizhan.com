<?php

namespace App\Services\Crm;

use App\Models\Person;
use App\Models\PersonFace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Stores face samples on the private disk. Every image is decoded and
 * re-encoded as WebP, which drops EXIF/GPS metadata from phone photos.
 */
class FaceStorage
{
    /**
     * @param  array<string, mixed>  $data  validated face payload (CrmRules::face)
     */
    public function create(Person $person, array $data, ?UploadedFile $image = null, ?string $imageBinary = null): PersonFace
    {
        return DB::transaction(function () use ($person, $data, $image, $imageBinary): PersonFace {
            $payload = collect($data)->except(['image'])->all();

            if (isset($payload['embedding'])) {
                $payload['embedding'] = array_map('floatval', array_values((array) $payload['embedding']));
                $payload['embedding_dim'] = count($payload['embedding']);
            }

            $payload['source'] ??= 'manual';
            $payload['person_id'] = $person->id;

            if ($image || $imageBinary !== null) {
                $payload['image_path'] = $this->storeImage($person, $image, $imageBinary);
            }

            $makePrimary = (bool) ($payload['is_primary'] ?? false) || ! $person->faces()->exists();
            $payload['is_primary'] = $makePrimary;

            if ($makePrimary) {
                $person->faces()->update(['is_primary' => false]);
            }

            return PersonFace::query()->create($payload);
        });
    }

    public function makePrimary(PersonFace $face): void
    {
        DB::transaction(function () use ($face): void {
            PersonFace::query()->where('person_id', $face->person_id)->update(['is_primary' => false]);
            $face->forceFill(['is_primary' => true])->save();
        });
    }

    /**
     * Soft-delete the record; the private file is removed right away because a
     * biometric sample should not outlive the user's decision to delete it.
     */
    public function delete(PersonFace $face): void
    {
        $face->deleteImageFile();
        $face->forceFill(['image_path' => null])->save();
        $face->delete();
    }

    private function storeImage(Person $person, ?UploadedFile $file, ?string $binary): string
    {
        $manager = new ImageManager(new Driver);
        $image = $file ? $manager->read($file->getRealPath()) : $manager->read($binary);
        $image->scaleDown(1600, 1600);

        $path = trim((string) config('crm.faces.directory'), '/').'/'.$person->id.'/'.Str::uuid()->toString().'.webp';
        Storage::disk(PersonFace::disk())->put($path, (string) $image->toWebp(88));

        return $path;
    }
}
