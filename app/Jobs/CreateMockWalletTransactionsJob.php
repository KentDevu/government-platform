<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateMockWalletTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TRANSACTION_BATCH_SIZE = 500;
    private const TYPES = ['debit', 'credit'];

    public $tries = 3;
    public $backoff = [10, 60, 300];
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $maxTransactionsPerUser,
        protected ?string $role = null,
    ) {
        // Validate role parameter
        $validRoles = ['Administrator', 'Staff', 'Helper', 'Guard'];
        if ($this->role && !in_array($this->role, $validRoles)) {
            throw new \InvalidArgumentException("Invalid role: {$this->role}");
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $totalTransactionsCreated = 0;
        $usersProcessed = 0;

        // Filter users by role if specified
        $query = User::query();
        if ($this->role && $this->role !== 'all') {
            $query->where('role', $this->role);
        }

        // Process users and create transactions
        $query->chunk(100, function ($users) use (&$totalTransactionsCreated, &$usersProcessed) {
            foreach ($users as $user) {
                // Generate random number of transactions (1 to max)
                $transactionCount = rand(1, $this->maxTransactionsPerUser);

                // Create transactions for this user
                $transactions = [];
                for ($i = 0; $i < $transactionCount; $i++) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'description' => $this->generateDescription(),
                        'type' => self::TYPES[array_rand(self::TYPES)],
                        'value' => (float) number_format(rand(100, 100000) / 100, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert transactions
                if (!empty($transactions)) {
                    WalletTransaction::insert($transactions);
                    $totalTransactionsCreated += count($transactions);
                }

                $usersProcessed++;
            }
        });

        \Log::info('CreateMockWalletTransactionsJob completed', [
            'max_per_user' => $this->maxTransactionsPerUser,
            'role' => $this->role ?? 'all',
            'users_processed' => $usersProcessed,
            'total_transactions_created' => $totalTransactionsCreated,
        ]);

        // Chain the balance calculation job
        $this->chain([new CalculateUserBalanceJob()]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('CreateMockWalletTransactionsJob failed', [
            'max_per_user' => $this->maxTransactionsPerUser,
            'role' => $this->role ?? 'all',
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Generate a random transaction description.
     */
    private function generateDescription(): string
    {
        $descriptions = [
            'Payment for services',
            'Withdrawal',
            'Deposit',
            'Transfer',
            'Refund',
            'Commission',
            'Bonus',
            'Fee',
            'Interest',
            'Adjustment',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
