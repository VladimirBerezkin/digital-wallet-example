<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Seed test users for the application.
     */
    public function run(): void
    {
        // Test User 1 - Alice (has balance)
        User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'balance' => '1000.0000',
            'email_verified_at' => now(),
        ]);

        // Test User 2 - Bob (has balance)
        User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'balance' => '500.0000',
            'email_verified_at' => now(),
        ]);

        // Test User 3 - Charlie (no balance)
        User::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password'),
            'balance' => '0.0000',
            'email_verified_at' => now(),
        ]);

        // Test User 4 - Diana (large balance)
        User::create([
            'name' => 'Diana Prince',
            'email' => 'diana@example.com',
            'password' => Hash::make('password'),
            'balance' => '10000.0000',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Created 4 test users (password: "password")');
        $this->command->info('   - alice@example.com (Balance: $1,000.00)');
        $this->command->info('   - bob@example.com (Balance: $500.00)');
        $this->command->info('   - charlie@example.com (Balance: $0.00)');
        $this->command->info('   - diana@example.com (Balance: $10,000.00)');
    }
}
