<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:user-names {--email=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Update user names to uppercase. Optionally filter by email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');

        if ($email) {
            $this->updateSingleUser($email);
        } else {
            $this->updateAllUsers();
        }

        return self::SUCCESS;
    }

    /**
     * Update a single user's name by email.
     */
    private function updateSingleUser(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return;
        }

        $newName = strtoupper(fake()->name());
        $user->update(['name' => $newName]);

        $this->info("Updated user '{$email}' with new name: '{$newName}'");
    }

    /**
     * Update all users' names.
     */
    private function updateAllUsers(): void
    {
        $this->info('Updating all users names...');

        $count = 0;
        $bar = $this->output->createProgressBar(User::count());

        User::query()->chunk(100, function ($users) use (&$count, $bar) {
            foreach ($users as $user) {
                $newName = strtoupper(fake()->name());
                $user->update(['name' => $newName]);
                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$count} users' names!");
    }
}
