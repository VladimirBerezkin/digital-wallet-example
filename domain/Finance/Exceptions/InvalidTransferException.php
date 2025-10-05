<?php

declare(strict_types=1);

namespace Domain\Finance\Exceptions;

use Exception;

final class InvalidTransferException extends Exception
{
    public static function cannotTransferToSelf(): self
    {
        return new self('Cannot transfer money to yourself');
    }

    public static function receiverNotFound(int $receiverId): self
    {
        return new self("Receiver with ID {$receiverId} not found");
    }
}
