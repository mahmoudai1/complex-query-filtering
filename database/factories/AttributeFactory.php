<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(['text', 'number', 'boolean', 'date', 'select']),
            'options' => $this->faker->randomElement([
                json_encode(['Option 6', 'Option 7', 'Option 8']),
                json_encode(['Option 1', 'Option 2', 'Option 3']),
                json_encode(['Option 4', 'Option 5', 'Option 6']),
                json_encode(['Option 3', 'Option 4', 'Option 5'])]),
        ];
    }
}
