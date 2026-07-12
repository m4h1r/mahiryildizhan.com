<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'body' => fake()->paragraph(),
            'is_approved' => false,
        ];
    }
}
