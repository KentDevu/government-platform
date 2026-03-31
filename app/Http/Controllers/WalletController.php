<?php

namespace App\Http\Controllers;

use App\Jobs\CreateMockWalletTransactionsJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WalletController extends Controller
{
    /**
     * Show the wallet transaction creation form.
     */
    public function index(): View
    {
        // Get only roles that actually exist in the users table
        $roles = User::distinct('role')
            ->orderBy('role')
            ->pluck('role')
            ->toArray();

        // Always include 'All' as first option
        $rolesWithAll = array_merge(['All'], $roles);

        return view('wallet.transactions-form', [
            'roles' => $rolesWithAll,
        ]);
    }

    /**
     * Store and dispatch wallet transaction job.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_transactions' => ['required', 'integer', 'min:1', 'max:100'],
            'role' => ['required', 'string', 'in:All,Administrator,Staff,Helper,Guard'],
        ], [
            'max_transactions.required' => 'Max transactions is required',
            'max_transactions.integer' => 'Max transactions must be a number',
            'max_transactions.min' => 'Max transactions must be at least 1',
            'max_transactions.max' => 'Max transactions cannot exceed 100',
            'role.required' => 'Role is required',
            'role.in' => 'Invalid role selected',
        ]);

        $role = $validated['role'] === 'All' ? null : $validated['role'];

        // Audit trail: Log who dispatched this job
        \Log::info('Wallet transactions job dispatched', [
            'dispatched_by_user_id' => auth()->id(),
            'dispatched_by_email' => auth()->user()->email,
            'max_transactions' => $validated['max_transactions'],
            'role' => $role ?? 'All',
        ]);

        // Dispatch the job with chaining (automatically triggers CalculateUserBalanceJob after)
        CreateMockWalletTransactionsJob::dispatch(
            $validated['max_transactions'],
            $role
        );

        return redirect()
            ->route('admin.wallet.index')
            ->with('success', "Wallet transaction generator queued! Max {$validated['max_transactions']} transactions per user for role: {$validated['role']}", );
    }
}
