<?php

declare(strict_types=1);

use Domain\Finance\Enums\LedgerType;
use Domain\Finance\Enums\TransactionStatus;
use Domain\Finance\Exceptions\InsufficientBalanceException;
use Domain\Finance\Exceptions\InvalidTransferException;
use Domain\Finance\Models\BalanceLedger;
use Domain\Finance\Models\CommissionLedger;
use Domain\Finance\Models\Transaction;
use Domain\Finance\Models\TransactionEvent;
use Domain\Finance\Services\TransferService;
use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('TransferService', function (): void {
    beforeEach(function (): void {
        $this->service = app(TransferService::class);
    });

    it('successfully transfers money between users', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '500.00']);

        $transaction = $this->service->transfer(
            sender: $sender,
            receiverId: $receiver->id,
            amount: '100.00',
            description: 'Test transfer'
        );

        // Check transaction
        expect($transaction->status)->toBe(TransactionStatus::Completed)
            ->and($transaction->amount)->toBe('100.0000')
            ->and($transaction->commission_fee)->toBe('1.5000')
            ->and($transaction->total_debited)->toBe('101.5000');

        // Check balances
        $sender->refresh();
        $receiver->refresh();

        expect($sender->balance)->toBe('898.5000') // 1000 - 100 - 1.5
            ->and($receiver->balance)->toBe('600.0000'); // 500 + 100
    });

    it('creates ledger entries for both users', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '500.00']);

        $transaction = $this->service->transfer($sender, $receiver->id, '100.00');

        // Check sender ledger
        $senderLedger = BalanceLedger::where('user_id', $sender->id)
            ->where('transaction_id', $transaction->id)
            ->first();

        expect($senderLedger->type)->toBe(LedgerType::Debit)
            ->and($senderLedger->amount)->toBe('-101.5000')
            ->and($senderLedger->balance_before)->toBe('1000.0000')
            ->and($senderLedger->balance_after)->toBe('898.5000');

        // Check receiver ledger
        $receiverLedger = BalanceLedger::where('user_id', $receiver->id)
            ->where('transaction_id', $transaction->id)
            ->first();

        expect($receiverLedger->type)->toBe(LedgerType::Credit)
            ->and($receiverLedger->amount)->toBe('100.0000')
            ->and($receiverLedger->balance_before)->toBe('500.0000')
            ->and($receiverLedger->balance_after)->toBe('600.0000');
    });

    it('throws exception for insufficient balance', function (): void {
        $sender = User::factory()->create(['balance' => '50.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $this->service->transfer($sender, $receiver->id, '100.00');
    })->throws(InsufficientBalanceException::class);

    it('throws exception when transferring to self', function (): void {
        $user = User::factory()->create(['balance' => '1000.00']);

        $this->service->transfer($user, $user->id, '100.00');
    })->throws(InvalidTransferException::class);

    it('throws exception for non-existent receiver', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);

        $this->service->transfer($sender, 99999, '100.00');
    })->throws(InvalidTransferException::class);

    it('handles concurrent transfers correctly', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver1 = User::factory()->create(['balance' => '0']);
        $receiver2 = User::factory()->create(['balance' => '0']);

        // Simulate concurrent requests
        DB::transaction(function () use ($sender, $receiver1): void {
            $this->service->transfer($sender, $receiver1->id, '100.00');
        });

        DB::transaction(function () use ($sender, $receiver2): void {
            $this->service->transfer($sender, $receiver2->id, '100.00');
        });

        $sender->refresh();

        // Both transfers should succeed with proper locking
        expect($sender->balance)->toBe('797.0000'); // 1000 - (100+1.5)*2
    });

    it('records all events during transfer', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '500.00']);

        $transaction = $this->service->transfer($sender, $receiver->id, '100.00');

        // Check that events were recorded
        $events = TransactionEvent::where('transaction_id', $transaction->id)
            ->orderBy('created_at')
            ->get();

        expect($events)->toHaveCount(5)
            ->and($events[0]->event_type)->toBe('initiated')
            ->and($events[1]->event_type)->toBe('validated')
            ->and($events[2]->event_type)->toBe('debited')
            ->and($events[3]->event_type)->toBe('credited')
            ->and($events[4]->event_type)->toBe('completed');

        // Check event data integrity
        $initiatedEvent = $events[0];
        expect($initiatedEvent->event_data['sender_id'])->toBe($sender->id)
            ->and($initiatedEvent->event_data['amount'])->toBe('100.0000');
    });

    it('records commission in commission ledger', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $transaction = $this->service->transfer($sender, $receiver->id, '100.00');

        // Check commission was recorded
        $commission = CommissionLedger::where('transaction_id', $transaction->id)->first();

        expect($commission)->not->toBeNull()
            ->and($commission->amount)->toBe('1.5000')
            ->and($commission->status)->toBe('collected')
            ->and($commission->collected_at)->not->toBeNull();
    });

    it('can retrieve event history for transaction', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $transaction = $this->service->transfer($sender, $receiver->id, '100.00');

        $history = $this->service->getEventHistory($transaction);

        expect($history)->toBeArray()
            ->and($history)->toHaveCount(5)
            ->and($history[0]['event'])->toBe('initiated');
    });
});
