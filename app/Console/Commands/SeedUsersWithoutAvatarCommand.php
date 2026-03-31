<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SeedUsersWithoutAvatarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:users-without-avatar';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create 500 mock users without avatars (250 with staff role, 250 without)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating 500 users without avatars...');

        // Get the staff role
        $staffRole = Role::where('name', 'staff')->first();

        if (!$staffRole) {
            $this->error('Staff role not found. Run php artisan db:seed --class=RbacSeeder first.');
            return self::FAILURE;
        }

        // Create 250 users WITH staff role
        $this->info('Creating 250 users WITH staff role...');
        $usersWithStaff = User::factory(250)->withoutAvatar()->make();
        foreach ($usersWithStaff as $user) {
            $user->save();
            $user->roles()->attach($staffRole->id);
        }

        // Create 250 users WITHOUT staff role
        $this->info('Creating 250 users WITHOUT staff role...');
        User::factory(250)->withoutAvatar()->create();

        $this->info('Successfully created 500 users without avatars! (250 staff, 250 regular)');

        return self::SUCCESS;
    }
}
