<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserAvatarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:user-avatars {--email=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Update user avatars with fake PNG files. Optionally filter by email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');

        if ($email) {
            $this->updateSingleUserAvatar($email);
        } else {
            $this->updateAllUserAvatars();
        }

        return self::SUCCESS;
    }

    /**
     * Update a single user's avatar by email.
     */
    private function updateSingleUserAvatar(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return;
        }

        $avatarPath = $this->generateAvatarPath();
        $user->update(['avatar_path' => $avatarPath]);

        $this->info("Updated user '{$email}' with new avatar: '{$avatarPath}'");
    }

    /**
     * Update all users' avatars.
     */
    private function updateAllUserAvatars(): void
    {
        $this->info('Updating all users avatars...');

        $count = 0;
        $bar = $this->output->createProgressBar(User::count());

        User::query()->chunk(100, function ($users) use (&$count, $bar) {
            foreach ($users as $user) {
                $avatarPath = $this->generateAvatarPath();
                $user->update(['avatar_path' => $avatarPath]);
                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$count} users' avatars!");
    }

    /**
     * Generate a fake avatar and return its path.
     */
    private function generateAvatarPath(): string
    {
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

        if (!file_exists($sourceImage)) {
            return null;
        }

        $fileName = 'avatar_' . uniqid() . '.jpg';
        $destinationPath = 'avatars/' . $fileName;

        // Copy the public asset to the storage path
        copy($sourceImage, storage_path('app/public/' . $destinationPath));

        return $destinationPath;
    }
}
