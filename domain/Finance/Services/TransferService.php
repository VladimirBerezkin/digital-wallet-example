<?php

declare(strict_types=1);

namespace Domain\Finance\Services;

use Domain\Finance\Enums\LedgerType;
use Domain\Finance\Enums\TransactionStatus;
use Domain\Finance\Events\TransferCompleted;
use Domain\Finance\Exceptions\InsufficientBalanceException;
use Domain\Finance\Exceptions\InvalidTransferException;
use Domain\Finance\Models\BalanceLedger;
use Domain\Finance\Models\CommissionLedger;
use Domain\Finance\Models\Transaction;
use Domain\Identity\Models\User;
use Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/**
 * Transfer Service (Finance Domain)
 *
 * Orchestrates money transfers between users across domain boundaries.
 */
final readonly class TransferService
{
    private const COMMISSION_RATE = '0.015'; // 1.5%

    public function __construct(
        private EventService $eventService
    ) {}

    /**
     * Transfer money from sender to receiver.
     *
     * @throws InsufficientBalanceException
     * @throws InvalidTransferException
     */
    public function transfer(
        User $sender,
        int $receiverId,
        string $amount,
        ?string $description = null
    ): Transaction {
        return DB::transaction(function () use ($sender, $receiverId, $amount, $description): Transaction {
            // Validate not sending to self
            if ($sender->id === $receiverId) {
                throw InvalidTransferException::cannotTransferToSelf();
            }

            // Lock users in consistent order to prevent deadlocks
            $userIds = [$sender->id, $receiverId];
            sort($userIds);

            $users = User::whereIn('id', $userIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lockedSender = $users[$sender->id];
            $receiver = $users[$receiverId] ?? null;

            if (! $receiver) {
                throw InvalidTransferException::receiverNotFound($receiverId);
            }

            // Calculate amounts using Money value object
            $transferAmount = new Money($amount);
            $commission = $transferAmount->multiply(self::COMMISSION_RATE);
            $totalDebit = $transferAmount->add($commission);

            // Check sufficient balance
            $senderBalance = new Money($lockedSender->balance);
            if ($senderBalance->lessThan($totalDebit)) {
                throw InsufficientBalanceException::forUser(
                    $sender->id,
                    $totalDebit->getAmount(),
                    $senderBalance->getAmount()
                );
            }

            // Create transaction record with pending status
            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $transferAmount->getAmount(),
                'commission_fee' => $commission->getAmount(),
                'total_debited' => $totalDebit->getAmount(),
                'status' => TransactionStatus::Pending,
                'description' => $description,
            ]);

            // EVENT SOURCING: Record transaction initiated
            $this->eventService->record($transaction, 'initiated', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $transferAmount->getAmount(),
                'commission' => $commission->getAmount(),
                'sender_balance_before' => $senderBalance->getAmount(),
                'receiver_balance_before' => (new Money($receiver->balance))->getAmount(),
            ]);

            // EVENT SOURCING: Record validation passed
            $this->eventService->record($transaction, 'validated', [
                'validation_checks' => [
                    'sufficient_balance' => true,
                    'not_self_transfer' => true,
                    'receiver_exists' => true,
                ],
            ]);

            // Update balances
            $lockedSender->decrement('balance', $totalDebit->getAmount());

            // EVENT SOURCING: Record debit
            $this->eventService->record($transaction, 'debited', [
                'user_id' => $sender->id,
                'amount' => $totalDebit->getAmount(),
                'balance_before' => $senderBalance->getAmount(),
            ]);

            $receiver->increment('balance', $transferAmount->getAmount());

            // EVENT SOURCING: Record credit
            $this->eventService->record($transaction, 'credited', [
                'user_id' => $receiver->id,
                'amount' => $transferAmount->getAmount(),
            ]);

            // Reload fresh balances
            $lockedSender->refresh();
            $receiver->refresh();

            // Store receiver's original balance before the transfer
            $receiverBalanceBefore = new Money($receiver->balance)->subtract($transferAmount);

            // Create ledger entries (audit trail)
            BalanceLedger::create([
                'user_id' => $sender->id,
                'transaction_id' => $transaction->id,
                'amount' => '-'.$totalDebit->getAmount(),
                'balance_before' => $senderBalance->getAmount(),
                'balance_after' => $lockedSender->balance,
                'type' => LedgerType::Debit,
            ]);

            BalanceLedger::create([
                'user_id' => $receiver->id,
                'transaction_id' => $transaction->id,
                'amount' => $transferAmount->getAmount(),
                'balance_before' => $receiverBalanceBefore->getAmount(),
                'balance_after' => $receiver->balance,
                'type' => LedgerType::Credit,
            ]);

            // Record commission in ledger
            CommissionLedger::create([
                'transaction_id' => $transaction->id,
                'amount' => $commission->getAmount(),
                'status' => 'collected',
                'collected_at' => now(),
            ]);

            // Update transaction status to completed
            $transaction->update([
                'status' => TransactionStatus::Completed,
            ]);

            // EVENT SOURCING: Record completion
            $this->eventService->record($transaction, 'completed', [
                'sender_balance_after' => $lockedSender->balance,
                'receiver_balance_after' => $receiver->balance,
                'commission_collected' => $commission->getAmount(),
                'completed_at' => now()->toIso8601String(),
            ]);

            // Dispatch event after transaction commit for real-time broadcasting
            DB::afterCommit(function () use ($transaction): void {
                event(new TransferCompleted($transaction));
            });

            return $transaction;
        });
    }

    /**
     * Get event history for a transaction (for debugging/auditing).
     *
     * @return array<int, array{event: string, data: array, timestamp: string}>
     */
    public function getEventHistory(Transaction $transaction): array
    {
        return $this->eventService->getHistory($transaction);
    }
}
