<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Location::class;
    public function definition(): array
    {
        return [
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->country,
        ];
    }
}
