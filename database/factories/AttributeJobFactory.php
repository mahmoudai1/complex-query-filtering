<?php

namespace Database\Factories;

use App\Models\AttributeJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeJob>
 */
class AttributeJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = AttributeJob::class;

    public function definition(): array
    {
        return [
            'job_id' => \App\Models\Job::factory(),
            'attribute_id' => \App\Models\Attribute::factory(),
            'value' => $this->faker->word,
        ];
    }
}
