<?php

namespace Database\Factories;

use App\Models\RecentLaw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecentLaw>
 */
class RecentLawFactory extends Factory
{
    protected $model = RecentLaw::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->numerify('###-####'),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'active', 'archived']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
