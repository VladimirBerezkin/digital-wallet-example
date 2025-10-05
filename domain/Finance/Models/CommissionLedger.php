<?php

declare(strict_types=1);

namespace Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Commission Ledger Entity (Finance Domain)
 *
 * Tracks all commission revenue.
 */
final class CommissionLedger extends Model
{
    protected $table = 'commission_ledgers';

    protected $fillable = [
        'transaction_id',
        'amount',
        'status',
        'collected_at',
    ];

    public function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'collected_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
