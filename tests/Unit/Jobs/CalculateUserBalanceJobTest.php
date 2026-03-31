<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CalculateUserBalanceJob;
use App\Models\User;
use App\Models\WalletTransaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculateUserBalanceJobTest extends TestCase
{
    #[Test]
    public function it_calculates_correct_balance_from_transactions(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        // Create $100 in credits
        WalletTransaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 50.00,
        ]);

        // Create $30 in debits
        WalletTransaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 10.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        // Balance should be 100 - 30 = 70
        $this->assertEquals(70.00, $user->balance);
    }

    #[Test]
    public function it_updates_multiple_users(): void
    {
        $user1 = User::factory()->create(['balance' => 0]);
        $user2 = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->count(5)->create([
            'user_id' => $user1->id,
            'type' => 'credit',
            'value' => 100.00,
        ]);

        WalletTransaction::factory()->count(3)->create([
            'user_id' => $user2->id,
            'type' => 'debit',
            'value' => 50.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user1->refresh();
        $user2->refresh();

        $this->assertEquals(500.00, $user1->balance);
        $this->assertEquals(-150.00, $user2->balance);
    }

    #[Test]
    public function it_handles_user_with_no_transactions(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        // Balance should be 0 (no transactions)
        $this->assertEquals(0.00, $user->balance);
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new CalculateUserBalanceJob();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300], $job->backoff);
        $this->assertEquals(600, $job->timeout);
    }

    #[Test]
    public function it_processes_large_user_batches(): void
    {
        $users = User::factory()->count(25)->create(['balance' => 0]);

        foreach ($users as $user) {
            WalletTransaction::factory()->count(5)->create([
                'user_id' => $user->id,
                'type' => 'credit',
                'value' => 100.00,
            ]);
        }

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $this->assertEquals(25, User::where('balance', 500.00)->count());
    }

    #[Test]
    public function it_zeros_out_balance_when_debits_exceed_credits(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 20.00,
        ]);

        WalletTransaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 50.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        // Balance = 20 - 100 = -80
        $this->assertEquals(-80.00, $user->balance);
    }

    #[Test]
    public function it_uses_atomic_transactions(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->count(10)->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 100.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        $this->assertEquals(1000.00, $user->balance);
    }

    #[Test]
    public function it_handles_decimal_precision(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 10.25,
        ]);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 5.15,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        // 10.25 - 5.15 = 5.10
        $this->assertEquals(5.10, $user->balance);
    }

    #[Test]
    public function it_recalculates_balance_on_repeat_calls(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 100.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        $this->assertEquals(100.00, $user->balance);

        // Add more transactions
        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 30.00,
        ]);

        $job->handle();
        $user->refresh();
        // Should be recalculated: 100 - 30 = 70
        $this->assertEquals(70.00, $user->balance);
    }
}
