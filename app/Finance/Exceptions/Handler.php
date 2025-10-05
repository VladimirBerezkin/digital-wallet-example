<?php

declare(strict_types=1);

namespace App\Finance\Exceptions;

use Domain\Finance\Exceptions\InsufficientBalanceException;
use Domain\Finance\Exceptions\InvalidTransferException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Finance Domain Exception Handler
 *
 * Converts domain exceptions into proper HTTP responses.
 */
final class Handler
{
    /**
     * Handle domain exceptions and return appropriate HTTP responses.
     */
    public static function handle(Throwable $exception): ?JsonResponse
    {
        return match (true) {
            $exception instanceof InvalidTransferException => self::handleInvalidTransfer($exception),
            $exception instanceof InsufficientBalanceException => self::handleInsufficientBalance($exception),
            default => null,
        };
    }

    /**
     * Handle invalid transfer exceptions.
     */
    private static function handleInvalidTransfer(InvalidTransferException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => [
                'receiver_id' => [$exception->getMessage()],
            ],
        ], 422);
    }

    /**
     * Handle insufficient balance exceptions.
     */
    private static function handleInsufficientBalance(InsufficientBalanceException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => [
                'amount' => [$exception->getMessage()],
            ],
        ], 422);
    }
}
