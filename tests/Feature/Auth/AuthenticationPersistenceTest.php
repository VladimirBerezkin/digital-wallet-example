<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Authentication Persistence', function (): void {
    it('maintains token across multiple requests', function (): void {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'alice@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $token = $response->json('token');
        expect($token)->not->toBeNull();

        // Make another request with the same token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/auth/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    });

    it('clears token on logout', function (): void {
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create token directly
        $token = $user->createToken('test-token')->plainTextToken;

        // Verify token exists before logout
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);

        // Logout with the token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/auth/logout');
        $response->assertOk();

        // Token should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);
    });
});
