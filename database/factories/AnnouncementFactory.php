<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->word(),
            'category_color' => fake()->randomElement(['primary', 'success', 'danger', 'warning', 'info']),
            'title' => fake()->sentence(),
            'excerpt' => fake()->paragraph(),
            'date' => fake()->date(),
            'image' => null,
            'image_alt' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the announcement should have an image.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => fake()->imageUrl(),
            'image_alt' => fake()->sentence(3),
        ]);
    }
}
