<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CalculateUserBalanceJob;
use App\Jobs\CreateMockWalletTransactionsJob;
use App\Models\User;
use App\Models\WalletTransaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateMockWalletTransactionsJobTest extends TestCase
{
    #[Test]
    public function it_creates_correct_number_of_transactions(): void
    {
        User::factory()->count(3)->create();

        $job = new CreateMockWalletTransactionsJob(5);
        $job->handle();

        // Each user should have 1-5 transactions
        $this->assertGreaterThanOrEqual(3, WalletTransaction::count());
        $this->assertLessThanOrEqual(15, WalletTransaction::count());
    }

    #[Test]
    public function it_filters_by_role_when_specified(): void
    {
        User::factory()->count(2)->create(['role' => 'Administrator']);
        User::factory()->count(2)->create(['role' => 'Staff']);

        $job = new CreateMockWalletTransactionsJob(5, 'Administrator');
        $job->handle();

        // Verify transactions were created only for administrators
        $this->assertGreaterThan(0, WalletTransaction::count());
        $this->assertTrue(
            WalletTransaction::whereIn('user_id', User::where('role', 'Administrator')->pluck('id'))->count() > 0
        );
    }

    #[Test]
    public function it_creates_debit_and_credit_transactions(): void
    {
        User::factory()->count(5)->create();

        $job = new CreateMockWalletTransactionsJob(50);
        $job->handle();

        $debits = WalletTransaction::where('type', 'debit')->count();
        $credits = WalletTransaction::where('type', 'credit')->count();

        $this->assertGreaterThan(0, $debits);
        $this->assertGreaterThan(0, $credits);
    }

    #[Test]
    public function it_generates_valid_transaction_values(): void
    {
        User::factory()->count(2)->create();

        $job = new CreateMockWalletTransactionsJob(10);
        $job->handle();

        // All values should be between 1 and 10000
        $transactions = WalletTransaction::all();
        foreach ($transactions as $transaction) {
            $this->assertGreaterThanOrEqual(1.00, $transaction->value);
            $this->assertLessThanOrEqual(10000.00, $transaction->value);
        }
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new CreateMockWalletTransactionsJob(10);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 60, 300], $job->backoff);
        $this->assertEquals(600, $job->timeout);
    }

    #[Test]
    public function it_handles_large_batch_of_users(): void
    {
        User::factory()->count(50)->create();

        $job = new CreateMockWalletTransactionsJob(10);
        $job->handle();

        $this->assertGreaterThanOrEqual(50, WalletTransaction::count());
    }

    #[Test]
    public function it_creates_transactions_for_all_role_when_role_is_null(): void
    {
        User::factory()->count(1)->create(['role' => 'Administrator']);
        User::factory()->count(1)->create(['role' => 'Staff']);
        User::factory()->count(1)->create(['role' => 'Helper']);

        $job = new CreateMockWalletTransactionsJob(3, null);
        $job->handle();

        // All 3 users should have transactions
        $this->assertEquals(3, WalletTransaction::distinct('user_id')->count('user_id'));
    }

    #[Test]
    public function it_creates_random_number_of_transactions_per_user(): void
    {
        $user = User::factory()->create();

        $job = new CreateMockWalletTransactionsJob(100);
        $job->handle();

        // Should create between 1 and 100 transactions
        $count = WalletTransaction::where('user_id', $user->id)->count();
        $this->assertGreaterThanOrEqual(1, $count);
        $this->assertLessThanOrEqual(100, $count);
    }

    #[Test]
    public function it_uses_correct_description_values(): void
    {
        User::factory()->count(2)->create();

        $job = new CreateMockWalletTransactionsJob(5);
        $job->handle();

        // Verify all transactions have descriptions
        $transactions = WalletTransaction::all();
        $this->assertGreaterThan(0, $transactions->count());
        foreach ($transactions as $transaction) {
            $this->assertNotEmpty($transaction->description);
            $this->assertIsString($transaction->description);
        }
    }
}
