<?php

declare(strict_types=1);

use App\Finance\Http\Controllers\TransactionController;
use App\Identity\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// CSRF token endpoint for frontend
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function (): void {
    // Finance domain routes
    Route::prefix('transactions')->group(function (): void {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
    });
});
