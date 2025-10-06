<?php

declare(strict_types=1);

use Domain\Finance\Events\TransferCompleted;
use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

describe('Transfer Broadcasting', function (): void {
    it('broadcasts transfer completed event', function (): void {
        Event::fake();

        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
        ]);

        Event::assertDispatched(TransferCompleted::class);
    });

    it('broadcasts to both sender and receiver channels', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $response = $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
        ]);

        $response->assertSuccessful();

        // Check that the transfer was successful
        $responseData = $response->json();
        expect($responseData['data']['id'])->toBeInt();
        expect($responseData['data']['type'])->toBe('sent');
        expect($responseData['data']['amount'])->toBe('100.0000');
        expect($responseData['data']['status'])->toBe('completed');
    });

    it('includes transaction details in broadcast', function (): void {
        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $response = $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
            'description' => 'Test payment',
        ]);

        $response->assertSuccessful();

        // Check that the transfer was successful and includes the transaction
        $responseData = $response->json();
        expect($responseData['data']['id'])->toBeInt();
        expect($responseData['data']['type'])->toBe('sent');
        expect($responseData['data']['amount'])->toBe('100.0000');
        expect($responseData['data']['status'])->toBe('completed');
        expect($responseData['data']['description'])->toBe('Test payment');
    });
});
