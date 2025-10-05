<?php

declare(strict_types=1);

namespace Domain\Finance\Models;

use Domain\Finance\Enums\LedgerType;
use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Balance Ledger Entity (Finance Domain)
 *
 * Audit trail for all balance changes.
 */
final class BalanceLedger extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'balance_before',
        'balance_after',
        'type',
    ];

    public function casts(): array
    {
        return [
            'type' => LedgerType::class,
            'amount' => 'decimal:4',
            'balance_before' => 'decimal:4',
            'balance_after' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
