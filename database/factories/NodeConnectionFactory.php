<?php

namespace Database\Factories;

use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

class NodeConnectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'node_from_id' => Node::factory(),
            'node_to_id' => Node::factory(),
        ];
    }
}
