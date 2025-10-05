<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear sessions table manually since RefreshDatabase might not clear it properly
    DB::table('sessions')->truncate();
});

describe('Authentication Persistence', function (): void {
    it('shows login form when not authenticated', function (): void {
        // Clear browser storage first
        $page = visit('/');
        $page->script('localStorage.clear();');
        $page->script('sessionStorage.clear();');

        // Refresh the page to ensure clean state
        $page->refresh();

        // Wait for the page to load and then check what's displayed
        $page->waitForText('Sign in to manage your wallet');

        // Should show the login form
        $page->assertSee('Sign in to manage your wallet')
            ->assertSee('Email Address')
            ->assertSee('Password');
    });

    it('logs in and maintains state after refresh', function (): void {
        $user = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        // Visit the application
        $page = visit('/');

        // Clear browser storage to ensure clean state
        $page->script('localStorage.clear();');
        $page->script('sessionStorage.clear();');

        // Should show login form initially
        $page->assertSee('Sign in to manage your wallet');

        // Login using the quick login button
        $page->click('Alice Johnson');

        // Wait for the login to complete and dashboard to load
        $page->waitForText('Digital Wallet');

        // Wait a bit more for the UI to fully update
        // $page->pause(1000);

        // Should now show the wallet dashboard
        $page->assertSee('Digital Wallet')
            ->assertSee($user->name);

        // Refresh the page
        $page->refresh();

        // Wait for the page to load after refresh
        $page->waitForText('Digital Wallet');

        // Should still be authenticated after refresh
        $page->assertSee('Digital Wallet')
            ->assertSee($user->name)
            ->assertDontSee('Sign in to manage your wallet');
    });

    it('logs out and clears authentication state', function (): void {
        $user = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        // Visit the application
        $page = visit('/');

        // Clear browser storage to ensure clean state
        $page->script('localStorage.clear();');
        $page->script('sessionStorage.clear();');

        // Refresh the page to ensure clean state
        $page->refresh();

        // Login first
        $page->click('Alice Johnson');
        $page->waitForText('Digital Wallet');
        // $page->pause(1000);

        // Should show the wallet dashboard
        $page->assertSee('Digital Wallet');

        // Logout
        $page->click('Logout');

        // Should show login form after logout
        $page->assertSee('Sign in to manage your wallet');

        // Refresh the page
        $page->refresh();

        // Should still show login form after refresh
        $page->assertSee('Sign in to manage your wallet');
    });
});
