<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model should have a fake avatar.
     */
    public function withAvatar(): static
    {
        return $this->state(function (array $attributes) {
            // List of available hero images
            $heroImages = [
                'hero-agencies.jpg',
                'hero-executive.jpg',
                'hero-judiciary.jpg',
                'hero-landing.jpg',
                'hero-legislative.jpg',
            ];

            // Randomly select an image
            $imageName = $heroImages[array_rand($heroImages)];
            $sourceImage = public_path('assets/img/' . $imageName);

            if (file_exists($sourceImage)) {
                $fileName = 'avatar_' . uniqid() . '.jpg';
                $destinationPath = 'avatars/' . $fileName;

                // Copy the public asset to the storage path
                copy($sourceImage, storage_path('app/public/' . $destinationPath));

                return [
                    'avatar_path' => $destinationPath,
                ];
            }

            return [
                'avatar_path' => null,
            ];
        });
    }

    /**
     * Indicate that the model should not have an avatar.
     */
    public function withoutAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar_path' => null,
        ]);
    }
}
