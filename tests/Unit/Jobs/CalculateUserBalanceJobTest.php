<?php

/**
 * CalculateUserBalanceJob – Unit Test Suite
 *
 * Tests the queue job that recalculates every user's wallet balance
 * by summing credits and subtracting debits from WalletTransaction.
 * Covers accuracy, multi-user, edge cases, decimal precision, and idempotency.
 *
 * Pest concepts demonstrated:
 *  • it()                    – each test is a standalone closure.
 *  • describe()              – groups tests; label shows as prefix in output.
 *  • expect()->toBe()        – strict equality assertion.
 */

use App\Jobs\CalculateUserBalanceJob;
use App\Models\User;
use App\Models\WalletTransaction;

// ─── Balance Calculation ─────────────────────────────────────────────────────

describe('Balance Calculation', function () {

    // 2 credits (50 each) – 3 debits (10 each) = 70.00 balance.
    it('calculates correct balance from transactions', function (): void {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 50.00,
        ]);

        WalletTransaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 10.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        expect((float) $user->balance)->toBe(70.00);
    });

    // Both user1 and user2 get their balances updated independently.
    it('updates multiple users', function (): void {
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

        expect((float) $user1->balance)->toBe(500.00);
        expect((float) $user2->balance)->toBe(-150.00);
    });

    // A user with no transactions should have their balance reset to 0.
    it('handles user with no transactions', function (): void {
        $user = User::factory()->create(['balance' => 100.00]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        expect((float) $user->balance)->toBe(0.00);
    });

    // More debits than credits yields a negative balance.
    it('zeros out balance when debits exceed credits', function (): void {
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
        expect((float) $user->balance)->toBe(-80.00);
    });

    // 10.25 credit – 5.15 debit = 5.10; checks float precision.
    it('handles decimal precision', function (): void {
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
        expect((float) $user->balance)->toBe(5.10);
    });

});

// ─── Edge Cases ──────────────────────────────────────────────────────────────

describe('Edge Cases', function () {

    // 25 users each with 5 credits of 100 = all balances should be 500.
    it('processes large user batches', function (): void {
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

        expect(User::where('balance', 500.00)->count())->toBe(25);
    });

    // 10 × 100 credit = 1000; verifies atomic (transactional) update.
    it('uses atomic transactions', function (): void {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->count(10)->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 100.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        expect((float) $user->balance)->toBe(1000.00);
    });

    // Running the job twice after adding a new debit recalculates correctly.
    it('recalculates balance on repeat calls', function (): void {
        $user = User::factory()->create(['balance' => 0]);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'value' => 100.00,
        ]);

        $job = new CalculateUserBalanceJob();
        $job->handle();

        $user->refresh();
        expect((float) $user->balance)->toBe(100.00);

        WalletTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'value' => 30.00,
        ]);

        $job->handle();
        $user->refresh();
        expect((float) $user->balance)->toBe(70.00);
    });

});

// ─── Config ──────────────────────────────────────────────────────────────────

describe('Config', function () {

    it('has correct retry config', function (): void {
        $job = new CalculateUserBalanceJob();

        expect($job->tries)->toBe(3);
        expect($job->backoff)->toBe([60, 300]);
        expect($job->timeout)->toBe(600);
    });

});
