<?php

declare(strict_types=1);

namespace App\Finance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isSender = $this->sender_id === $user->id;

        return [
            'id' => $this->id,
            'type' => $isSender ? 'sent' : 'received',
            'amount' => $this->amount,
            'commission' => $isSender ? $this->commission_fee : null,
            'total' => $isSender ? $this->total_debited : $this->amount,
            'counterparty' => $isSender ? $this->receiver->name : $this->sender?->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'date' => $this->created_at->toIso8601String(),
        ];
    }
}
