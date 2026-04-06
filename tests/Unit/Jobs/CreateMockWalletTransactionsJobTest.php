<?php

use App\Jobs\CreateMockWalletTransactionsJob;
use App\Models\User;
use App\Models\WalletTransaction;

it('creates correct number of transactions', function (): void {
    User::factory()->count(3)->create();

    $job = new CreateMockWalletTransactionsJob(5);
    $job->handle();

    expect(WalletTransaction::count())->toBeGreaterThanOrEqual(3);
    expect(WalletTransaction::count())->toBeLessThanOrEqual(15);
});

it('filters by role when specified', function (): void {
    User::factory()->count(2)->create(['role' => 'Administrator']);
    User::factory()->count(2)->create(['role' => 'Staff']);

    $job = new CreateMockWalletTransactionsJob(5, 'Administrator');
    $job->handle();

    expect(WalletTransaction::count())->toBeGreaterThan(0);
    expect(
        WalletTransaction::whereIn('user_id', User::where('role', 'Administrator')->pluck('id'))->count() > 0
    )->toBeTrue();
});

it('creates debit and credit transactions', function (): void {
    User::factory()->count(5)->create();

    $job = new CreateMockWalletTransactionsJob(50);
    $job->handle();

    $debits = WalletTransaction::where('type', 'debit')->count();
    $credits = WalletTransaction::where('type', 'credit')->count();

    expect($debits)->toBeGreaterThan(0);
    expect($credits)->toBeGreaterThan(0);
});

it('generates valid transaction values', function (): void {
    User::factory()->count(2)->create();

    $job = new CreateMockWalletTransactionsJob(10);
    $job->handle();

    $transactions = WalletTransaction::all();
    foreach ($transactions as $transaction) {
        expect((float) $transaction->value)->toBeGreaterThanOrEqual(1.00);
        expect((float) $transaction->value)->toBeLessThanOrEqual(10000.00);
    }
});

it('has correct retry config', function (): void {
    $job = new CreateMockWalletTransactionsJob(10);

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([10, 60, 300]);
    expect($job->timeout)->toBe(600);
});

it('handles large batch of users', function (): void {
    User::factory()->count(50)->create();

    $job = new CreateMockWalletTransactionsJob(10);
    $job->handle();

    expect(WalletTransaction::count())->toBeGreaterThanOrEqual(50);
});

it('creates transactions for all roles when role is null', function (): void {
    User::factory()->count(1)->create(['role' => 'Administrator']);
    User::factory()->count(1)->create(['role' => 'Staff']);
    User::factory()->count(1)->create(['role' => 'Helper']);

    $job = new CreateMockWalletTransactionsJob(3, null);
    $job->handle();

    expect(WalletTransaction::distinct('user_id')->count('user_id'))->toBe(3);
});

it('creates random number of transactions per user', function (): void {
    $user = User::factory()->create();

    $job = new CreateMockWalletTransactionsJob(100);
    $job->handle();

    $count = WalletTransaction::where('user_id', $user->id)->count();
    expect($count)->toBeGreaterThanOrEqual(1);
    expect($count)->toBeLessThanOrEqual(100);
});

it('uses correct description values', function (): void {
    User::factory()->count(2)->create();

    $job = new CreateMockWalletTransactionsJob(5);
    $job->handle();

    $transactions = WalletTransaction::all();
    expect($transactions->count())->toBeGreaterThan(0);

    foreach ($transactions as $transaction) {
        expect($transaction->description)->not->toBeEmpty();
        expect($transaction->description)->toBeString();
    }
});
