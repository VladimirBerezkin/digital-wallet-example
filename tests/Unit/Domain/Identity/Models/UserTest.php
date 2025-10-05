<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model (Identity Domain)', function (): void {
    it('creates a user with default balance', function (): void {
        $user = User::factory()->create();

        expect($user->balance)->toBe('0.0000')
            ->and($user->name)->not->toBeEmpty()
            ->and($user->email)->not->toBeEmpty();
    });

    it('creates a user with custom balance', function (): void {
        $user = User::factory()->create([
            'balance' => '1000.5000',
        ]);

        expect($user->balance)->toBe('1000.5000');
    });

    it('casts balance as decimal with 4 places', function (): void {
        $user = User::factory()->create([
            'balance' => '100.50',
        ]);

        expect($user->balance)->toBeString()
            ->and($user->balance)->toBe('100.5000');
    });

    it('has fillable attributes', function (): void {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'balance' => '500.0000',
        ]);

        expect($user->name)->toBe('John Doe')
            ->and($user->email)->toBe('john@example.com')
            ->and($user->balance)->toBe('500.0000');
    });

    it('hides password and remember_token', function (): void {
        $user = User::factory()->create();

        $array = $user->toArray();

        expect($array)->not->toHaveKey('password')
            ->and($array)->not->toHaveKey('remember_token');
    });

    it('has sentTransactions relationship method', function (): void {
        $user = User::factory()->create();

        expect(method_exists($user, 'sentTransactions'))->toBeTrue();
    });

    it('has receivedTransactions relationship method', function (): void {
        $user = User::factory()->create();

        expect(method_exists($user, 'receivedTransactions'))->toBeTrue();
    });

    it('has balanceLedgers relationship method', function (): void {
        $user = User::factory()->create();

        expect(method_exists($user, 'balanceLedgers'))->toBeTrue();
    });
});
