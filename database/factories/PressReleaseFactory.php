<?php

namespace Database\Factories;

use App\Models\PressRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PressRelease>
 */
class PressReleaseFactory extends Factory
{
    protected $model = PressRelease::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => fake()->company(),
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the press release should have a default URL.
     */
    public function withoutUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => '#',
        ]);
    }
}
