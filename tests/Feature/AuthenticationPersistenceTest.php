<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Authentication Persistence', function (): void {
    it('maintains session across multiple requests', function (): void {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        // Login
        $response = $this->withSession([])->postJson('/api/auth/login', [
            'email' => 'alice@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($user);

        // Get the session cookie
        $sessionCookie = $response->headers->getCookies()[0] ?? null;
        expect($sessionCookie)->not->toBeNull();

        // Make another request with the same session
        $response = $this->withCookies([
            $sessionCookie->getName() => $sessionCookie->getValue(),
        ])->getJson('/api/auth/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

        $this->assertAuthenticatedAs($user);
    });

    it('clears session on logout', function (): void {
        $user = User::factory()->create();

        // Login
        $response = $this->actingAs($user)->withSession([])->postJson('/api/auth/logout');
        $response->assertOk();

        // Should not be authenticated after logout
        $this->assertGuest();

        // Attempting to get user should fail
        $response = $this->getJson('/api/auth/user');
        $response->assertUnauthorized();
    });
});
