<?php

declare(strict_types=1);

namespace Domain\Finance\Services;

use Domain\Finance\Models\Transaction;
use Domain\Finance\Models\TransactionEvent;

/**
 * Event Service (Finance Domain)
 *
 * Handles event sourcing for transactions.
 */
final readonly class EventService
{
    /**
     * Record a transaction event.
     */
    public function record(Transaction $transaction, string $eventType, array $eventData = []): TransactionEvent
    {
        return TransactionEvent::create([
            'transaction_id' => $transaction->id,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'created_at' => now(),
        ]);
    }

    /**
     * Get the complete event history for a transaction.
     *
     * @return array<int, array{event: string, data: array, timestamp: string}>
     */
    public function getHistory(Transaction $transaction): array
    {
        return TransactionEvent::where('transaction_id', $transaction->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (TransactionEvent $event): array => [
                'event' => $event->event_type,
                'data' => $event->event_data,
                'timestamp' => $event->created_at->toIso8601String(),
            ])
            ->toArray();
    }
}
