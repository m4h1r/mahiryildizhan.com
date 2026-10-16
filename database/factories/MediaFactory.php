<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = $this->faker->slug(2);

        return [
            'filename' => $name.'.jpg',
            'path' => 'media/'.$name.'.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'checksum' => $this->faker->sha1(),
            'size' => $this->faker->numberBetween(10_000, 2_000_000),
            'width' => 1200,
            'height' => 800,
            'type' => 0,
            'alt' => $this->faker->sentence(3),
            'caption' => $this->faker->optional()->sentence(),
            'thumbnail_path' => 'media/'.$name.'-thumb.jpg',
            'webp_path' => 'media/'.$name.'.webp',
            'variant_paths' => null,
        ];
    }
}
