<?php

declare(strict_types=1);

namespace Domain\Finance\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
