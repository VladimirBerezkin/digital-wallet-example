<?php

declare(strict_types=1);

use Domain\Finance\Services\TransferService;
use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transaction API', function (): void {
    it('returns transaction history for authenticated user', function (): void {
        $user = User::factory()->create(['balance' => '1000.00']);

        // Create some transactions
        $receiver = User::factory()->create();
        app(TransferService::class)->transfer($user, $receiver->id, '100.00');

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertOk()
            ->assertJsonStructure([
                'balance',
                'transactions' => [
                    '*' => ['id', 'type', 'amount', 'counterparty', 'status', 'date'],
                ],
            ]);
    });

    it('creates a new transfer successfully', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $response = $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
            'description' => 'Test payment',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'amount', 'status'],
            ]);

        // Verify balances updated
        expect($sender->fresh()->balance)->toBe('898.5000')
            ->and($receiver->fresh()->balance)->toBe('100.0000');
    });

    it('validates transfer request', function (): void {
        $user = User::factory()->create(['balance' => '1000.00']);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'receiver_id' => 99999, // Non-existent
            'amount' => 'invalid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id', 'amount']);
    });

    it('prevents transferring with insufficient balance', function (): void {
        $sender = User::factory()->create(['balance' => '50.00']);
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    });

    it('prevents transferring to self', function (): void {
        $user = User::factory()->create(['balance' => '1000.00']);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'receiver_id' => $user->id,
            'amount' => '100.00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id']);
    });

    it('requires authentication', function (): void {
        $response = $this->getJson('/api/transactions');

        $response->assertUnauthorized();
    });

    it('returns only user\'s own transactions', function (): void {
        $user1 = User::factory()->create(['balance' => '1000.00']);
        $user2 = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        // User1 sends money
        app(TransferService::class)->transfer($user1, $receiver->id, '100.00');

        // User2 sends money
        app(TransferService::class)->transfer($user2, $receiver->id, '50.00');

        // User1 should only see their transaction
        $response = $this->actingAs($user1)->getJson('/api/transactions');

        $response->assertOk();
        $transactions = $response->json('transactions');

        expect($transactions)->toHaveCount(1)
            ->and($transactions[0]['amount'])->toBe('100.0000');
    });
});
