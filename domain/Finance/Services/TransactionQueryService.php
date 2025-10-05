<?php

declare(strict_types=1);

namespace Domain\Finance\Services;

use Domain\Finance\Models\Transaction;
use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Transaction Query Service (Finance Domain)
 *
 * Handles all transaction queries and retrieval logic.
 */
final readonly class TransactionQueryService
{
    /**
     * Get all transactions for a user (sent and received).
     *
     * @return Collection<int, Transaction>
     */
    public function getTransactionsForUser(User $user): Collection
    {
        return Transaction::query()
            ->where(function ($query) use ($user): void {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->latest()
            ->get();
    }

    /**
     * Get transactions sent by a user.
     *
     * @return Collection<int, Transaction>
     */
    public function getSentTransactions(User $user): Collection
    {
        return Transaction::query()
            ->where('sender_id', $user->id)
            ->with('receiver')
            ->latest()
            ->get();
    }

    /**
     * Get transactions received by a user.
     *
     * @return Collection<int, Transaction>
     */
    public function getReceivedTransactions(User $user): Collection
    {
        return Transaction::query()
            ->where('receiver_id', $user->id)
            ->with('sender')
            ->latest()
            ->get();
    }
}
