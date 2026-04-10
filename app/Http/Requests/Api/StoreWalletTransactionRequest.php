<?php

namespace App\Http\Requests\Api;

class StoreWalletTransactionRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'max_transactions' => ['required', 'integer', 'min:1', 'max:100'],
            'role' => ['required', 'string', 'in:All,Administrator,Staff,Helper,Guard'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_transactions.required' => 'Max transactions is required.',
            'max_transactions.integer' => 'Max transactions must be a number.',
            'max_transactions.min' => 'Max transactions must be at least 1.',
            'max_transactions.max' => 'Max transactions cannot exceed 100.',
            'role.required' => 'Role is required.',
            'role.in' => 'Invalid role selected.',
        ];
    }
}
