<?php

namespace Database\Factories;

use App\Models\TimeRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeRange>
 */
class TimeRangeFactory extends Factory
{
    protected $model = TimeRange::class;

    public function definition(): array
    {
        return [
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'starts_at' => '09:00',
            'ends_at' => '17:00',
            'label' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
        ];
    }
}
