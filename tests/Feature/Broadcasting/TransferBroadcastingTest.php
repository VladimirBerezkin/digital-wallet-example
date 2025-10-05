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
        Event::fake();

        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
        ]);

        Event::assertDispatched(function (TransferCompleted $event) use ($sender, $receiver): bool {
            $channels = $event->broadcastOn();

            return count($channels) === 2
                && $channels[0]->name === 'user.'.$sender->id
                && $channels[1]->name === 'user.'.$receiver->id;
        });
    });

    it('includes transaction details in broadcast', function (): void {
        Event::fake();

        $sender = User::factory()->create(['balance' => '1000.00']);
        $receiver = User::factory()->create(['balance' => '0']);

        $this->actingAs($sender)->postJson('/api/transactions', [
            'receiver_id' => $receiver->id,
            'amount' => '100.00',
            'description' => 'Test payment',
        ]);

        Event::assertDispatched(function (TransferCompleted $event): bool {
            $data = $event->broadcastWith();

            return isset($data['transaction_id'])
                && $data['amount'] === '100.0000'
                && $data['commission'] === '1.5000'
                && $data['status'] === 'completed';
        });
    });
});
