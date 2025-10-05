<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->restrictOnDelete();

            // Financial columns
            $table->decimal('amount', 19, 4); // Amount received
            $table->decimal('commission_fee', 19, 4)->default(0);
            $table->decimal('total_debited', 19, 4); // amount + commission

            // Status
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('failure_reason')->nullable();

            // Simple audit trail
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['sender_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
