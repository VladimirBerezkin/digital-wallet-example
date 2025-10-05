<?php

declare(strict_types=1);

use App\Finance\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    // Finance domain routes
    Route::prefix('transactions')->group(function (): void {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
    });
});
