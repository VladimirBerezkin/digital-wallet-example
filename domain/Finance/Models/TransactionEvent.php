<?php

declare(strict_types=1);

namespace Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction Event Entity (Finance Domain)
 *
 * Event sourcing for transaction state changes.
 */
final class TransactionEvent extends Model
{
    public $timestamps = false; // Only created_at

    protected $fillable = [
        'transaction_id',
        'event_type',
        'event_data',
        'created_at',
    ];

    public function casts(): array
    {
        return [
            'event_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
