<?php

declare(strict_types=1);

namespace Domain\Finance\Models;

use Domain\Finance\Enums\TransactionStatus;
use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Transaction Entity (Finance Domain)
 *
 * Represents a money transfer between users.
 */
final class Transaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'total_debited',
        'status',
        'failure_reason',
        'description',
    ];

    public function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'amount' => 'decimal:4',
            'commission_fee' => 'decimal:4',
            'total_debited' => 'decimal:4',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(BalanceLedger::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TransactionEvent::class);
    }

    public function commission(): HasMany
    {
        return $this->hasMany(CommissionLedger::class);
    }
}
