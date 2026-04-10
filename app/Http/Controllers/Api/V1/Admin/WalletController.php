<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\StoreWalletTransactionRequest;
use App\Jobs\CreateMockWalletTransactionsJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = User::distinct('role')
            ->orderBy('role')
            ->pluck('role')
            ->toArray();

        $rolesWithAll = array_merge(['All'], $roles);

        return $this->successResponse([
            'roles' => $rolesWithAll,
        ]);
    }

    public function store(StoreWalletTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = $validated['role'] === 'All' ? null : $validated['role'];

        Log::info('Wallet transactions job dispatched via API', [
            'dispatched_by_user_id' => auth()->id(),
            'dispatched_by_email' => auth()->user()->email,
            'max_transactions' => $validated['max_transactions'],
            'role' => $role ?? 'All',
        ]);

        CreateMockWalletTransactionsJob::dispatch(
            $validated['max_transactions'],
            $role,
        );

        return $this->acceptedResponse(
            "Wallet transaction generator queued. Max {$validated['max_transactions']} transactions per user for role: {$validated['role']}.",
        );
    }
}
