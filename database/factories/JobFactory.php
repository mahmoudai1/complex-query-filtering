<?php

namespace Database\Factories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'company_name' => $this->faker->company,
            'salary_min' => $this->faker->numberBetween(30000, 50000),
            'salary_max' => $this->faker->numberBetween(50000, 100000),
            'is_remote' => $this->faker->boolean,
            'job_type' => $this->faker->randomElement(['full-time', 'part-time', 'contract', 'freelance']),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at' => $this->faker->dateTimeThisYear,
        ];
    }
}
