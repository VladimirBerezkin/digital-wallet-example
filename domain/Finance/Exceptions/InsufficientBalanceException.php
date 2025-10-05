<?php

declare(strict_types=1);

namespace Domain\Finance\Exceptions;

use Exception;

final class InsufficientBalanceException extends Exception
{
    public static function forUser(int $userId, string $required, string $available): self
    {
        return new self(
            "User {$userId} has insufficient balance. Required: {$required}, Available: {$available}"
        );
    }
}
