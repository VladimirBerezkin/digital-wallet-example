<?php

declare(strict_types=1);

namespace Domain\Identity\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Domain\Finance\Models\BalanceLedger;
use Domain\Finance\Models\Transaction;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Entity (Identity Domain)
 *
 * Represents user identity and authentication.
 * Balance is stored here but managed by Finance domain.
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property string $balance
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:4',
        ];
    }

    /**
     * Cross-domain relationship: Transactions sent by this user.
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

    /**
     * Cross-domain relationship: Transactions received by this user.
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    /**
     * Cross-domain relationship: Balance ledger entries for this user.
     */
    public function balanceLedgers(): HasMany
    {
        return $this->hasMany(BalanceLedger::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
