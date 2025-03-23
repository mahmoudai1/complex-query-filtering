<?php

namespace Database\Seeders;

use App\Models\AttributeJob;
use Faker\Factory;
use App\Models\Job;

use App\Models\Category;
use App\Models\Language;
use App\Models\Location;
use App\Models\Attribute;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $faker;

    public function __construct(){
        $this->faker = Factory::create();
    }
    public function run(): void
    {
        Job::factory()->count(10)->create()->each(function ($job) {
            $languages = Language::factory()->create();
            $job->languages()->attach($languages, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $locations = Location::factory()->create();
            $job->locations()->attach($locations, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categories = Category::factory()->create();
            $job->categories()->attach($categories, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attributeType = $this->faker->randomElement(['text', 'number', 'boolean', 'date', 'select']);
            $attributes = Attribute::factory()->create($this->getAttributeFactory($attributeType));
            AttributeJob::factory()->create([
                'job_id' => $job->id,
                'attribute_id' => $attributes->id,
                'value' => $this->getAttributeJobValue($attributes->name, $attributes->type),
            ]);
        });
    }

    protected function getAttributeJobValue($name, $type)
    {
        switch ($type) {
            case 'text':
                return $this->faker->word;
            case 'number':
                return $this->faker->numberBetween(1, 10);
            case 'boolean':
                return $this->faker->boolean;
            case 'date':
                return $this->faker->date;
            case 'select':
                if($name == "level") {
                    return $this->faker->randomElement(['Entry', 'Mid', 'Senior']);
                } else if($name == "industry") {
                    return $this->faker->randomElement(['IT', 'HealthCare', 'Finance']);
                }
            default:
                return $this->faker->word;
        }
    }

    protected function getAttributeFactory($type){
        $name = $this->faker->word;
        $options = null;

        switch($type) {
            case 'text':
                $name = $this->faker->randomElement(['work_location', 'department']);
                break;
            case 'number':
                $name = $this->faker->randomElement(['years_experience', 'number_of_openings']);
                break;
            case 'boolean':
                $name = $this->faker->randomElement(['is_requires_degree', 'is_sponsorship']);
                break;
            case 'date':
                $name = $this->faker->randomElement(['deadline', 'start_date']);
                break;
            case 'select':
                $name = $this->faker->randomElement(['level', 'industry']);
                if($name == "level") {
                    $options = json_encode(['Entry', 'Mid', 'Senior']);
                } else if($name == "industry") {
                    $options = json_encode(['IT', 'HealthCare', 'Finance']);
                }
                break;
            default:
                break;
        }

        return [
            'name' => $name,
            'type' => $type,
            'options' => $options,
        ];
    }
}
