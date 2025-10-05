<?php

declare(strict_types=1);

namespace Domain\Finance\Events;

use Domain\Finance\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Transfer Completed Event (Finance Domain)
 *
 * Broadcast to both sender and receiver when transfer completes.
 */
final class TransferCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Transaction $transaction
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user.'.$this->transaction->sender_id),
            new Channel('user.'.$this->transaction->receiver_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'commission' => $this->transaction->commission_fee,
            'status' => $this->transaction->status->value,
            'sender_id' => $this->transaction->sender_id,
            'receiver_id' => $this->transaction->receiver_id,
        ];
    }
}
