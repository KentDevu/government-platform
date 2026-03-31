<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CalculateUserBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const USER_BATCH_SIZE = 100;

    public $tries = 3;
    public $backoff = [60, 300];
    public $timeout = 600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $usersUpdated = 0;
        $totalBalanceChanged = 0;

        // Process users in batches
        User::chunk(self::USER_BATCH_SIZE, function ($users) use (&$usersUpdated, &$totalBalanceChanged) {
            foreach ($users as $user) {
                // Calculate balance from transactions
                $balanceData = $user->calculateBalance();
                $newBalance = (float) $balanceData->total_credits - (float) $balanceData->total_debits;

                // Update user balance atomically
                DB::transaction(function () use ($user, $newBalance, &$totalBalanceChanged) {
                    $oldBalance = $user->balance;
                    $user->update(['balance' => $newBalance]);
                    $totalBalanceChanged += abs($newBalance - $oldBalance);
                });

                $usersUpdated++;
            }
        });

        \Log::info('CalculateUserBalanceJob completed', [
            'users_updated' => $usersUpdated,
            'total_balance_changed' => number_format($totalBalanceChanged, 2),
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('CalculateUserBalanceJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
