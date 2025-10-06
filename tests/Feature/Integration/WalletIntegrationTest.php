<?php

declare(strict_types=1);

use Domain\Finance\Models\BalanceLedger;
use Domain\Finance\Models\CommissionLedger;
use Domain\Finance\Models\Transaction;
use Domain\Finance\Models\TransactionEvent;
use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Wallet Integration Tests', function () {
    it('completes full transfer workflow end-to-end', function () {
        $sender = User::factory()->create([
            'name' => 'Alice',
            'balance' => '1000.00',
        ]);
        $receiver = User::factory()->create([
            'name' => 'Bob',
            'balance' => '500.00',
        ]);

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $sender->email,
            'password' => 'password',
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('token');

        // Get initial transactions
        $transactionsResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/transactions');

        $transactionsResponse->assertOk()
            ->assertJson([
                'balance' => '1000.0000',
                'transactions' => [],
            ]);

        // Perform transfer
        $transferResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
            'description' => 'Test payment',
        ]);

        $transferResponse->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'amount', 'status'],
            ]);

        // Verify balances updated
        $sender->refresh();
        $receiver->refresh();

        expect($sender->balance)->toBe('898.5000') // 1000 - 100 - 1.5
            ->and($receiver->balance)->toBe('600.0000'); // 500 + 100

        // Verify transaction created
        expect(Transaction::count())->toBe(1);

        $transaction = Transaction::first();
        expect($transaction->sender_id)->toBe($sender->id)
            ->and($transaction->receiver_id)->toBe($receiver->id)
            ->and($transaction->amount)->toBe('100.0000')
            ->and($transaction->commission_fee)->toBe('1.5000')
            ->and($transaction->total_debited)->toBe('101.5000')
            ->and($transaction->description)->toBe('Test payment');

        // Verify ledger entries created
        expect(BalanceLedger::count())->toBe(2);

        // Verify commission recorded
        expect(CommissionLedger::count())->toBe(1);
        $commission = CommissionLedger::first();
        expect($commission->amount)->toBe('1.5000')
            ->and($commission->status)->toBe('collected');

        // Verify events recorded
        expect(TransactionEvent::where('transaction_id', $transaction->id)->count())->toBe(5);

        // Get updated transactions
        $updatedTransactionsResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/transactions');

        $updatedTransactionsResponse->assertOk()
            ->assertJsonCount(1, 'transactions');

        // Verify the balance in the response matches expected
        $responseData = $updatedTransactionsResponse->json();
        expect($responseData['balance'])->toBe('898.5000');
    });

    it('handles multiple transfers correctly', function () {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver1 = User::factory()->create(['balance' => '0']);
        $receiver2 = User::factory()->create(['balance' => '0']);

        $token = $sender->createToken('test-token')->plainTextToken;

        // First transfer
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver1->id,
            'amount' => '100.00',
        ])->assertCreated();

        // Second transfer
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver2->id,
            'amount' => '200.00',
        ])->assertCreated();

        // Verify final balances
        $sender->refresh();
        $receiver1->refresh();
        $receiver2->refresh();

        expect($sender->balance)->toBe('695.5000') // 1000 - 100 - 1.5 - 200 - 3
            ->and($receiver1->balance)->toBe('100.0000')
            ->and($receiver2->balance)->toBe('200.0000');

        // Verify transaction count
        expect(Transaction::count())->toBe(2);
    });

    it('validates insufficient balance', function () {
        $sender = User::factory()->create(['balance' => '50.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $token = $sender->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);

        // Verify no transaction created
        expect(Transaction::count())->toBe(0);

        // Verify balances unchanged
        $sender->refresh();
        $receiver->refresh();

        expect($sender->balance)->toBe('50.0000')
            ->and($receiver->balance)->toBe('0.0000');
    });

    it('prevents self-transfer', function () {
        $user = User::factory()->create(['balance' => '1000.00']);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $user->id,
            'amount' => '100.00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id']);

        // Verify no transaction created
        expect(Transaction::count())->toBe(0);

        // Verify balance unchanged
        $user->refresh();
        expect($user->balance)->toBe('1000.0000');
    });

    it('validates receiver exists', function () {
        $sender = User::factory()->create(['balance' => '1000.00']);

        $token = $sender->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => 99999,
            'amount' => '100.00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id']);

        // Verify no transaction created
        expect(Transaction::count())->toBe(0);
    });

    it('requires authentication for all endpoints', function () {
        // Transactions endpoint
        $this->getJson('/api/transactions')->assertUnauthorized();
        $this->postJson('/api/transactions', [])->assertUnauthorized();

        // Auth user endpoint
        $this->getJson('/api/auth/user')->assertUnauthorized();
    });

    it('maintains data integrity across concurrent transfers', function () {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver1 = User::factory()->create(['balance' => '0']);
        $receiver2 = User::factory()->create(['balance' => '0']);

        $token = $sender->createToken('test-token')->plainTextToken;

        // Simulate concurrent transfers
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver1->id,
            'amount' => '100.00',
        ])->assertCreated();

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver2->id,
            'amount' => '150.00',
        ])->assertCreated();

        // Verify all ledger entries match
        $sender->refresh();
        $totalDebited = BalanceLedger::where('user_id', $sender->id)
            ->where('type', 'debit')
            ->sum('amount');

        $totalCredited = BalanceLedger::whereIn('user_id', [$receiver1->id, $receiver2->id])
            ->where('type', 'credit')
            ->sum('amount');

        // Verify final balance: 1000 - (100 + 1.5) - (150 + 2.25) = 746.25
        expect($sender->balance)->toBe('746.2500')
            ->and(abs((float) $totalDebited))->toBe(253.75) // 101.5 + 152.25
            ->and((float) $totalCredited)->toBe(250.0); // 100 + 150
    });

    it('records complete audit trail', function () {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $token = $sender->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
            'description' => 'Audit test',
        ])->assertCreated();

        $transaction = Transaction::first();

        // Verify all events recorded
        $events = TransactionEvent::where('transaction_id', $transaction->id)
            ->orderBy('created_at')
            ->get();

        expect($events)->toHaveCount(5)
            ->and($events->pluck('event_type')->toArray())->toBe([
                'initiated',
                'validated',
                'debited',
                'credited',
                'completed',
            ]);

        // Verify ledger entries
        expect(BalanceLedger::count())->toBe(2);

        // Verify commission ledger
        $commission = CommissionLedger::where('transaction_id', $transaction->id)->first();
        expect($commission)->not->toBeNull()
            ->and($commission->amount)->toBe('1.5000')
            ->and($commission->status)->toBe('collected');
    });
});
