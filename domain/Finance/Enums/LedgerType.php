<?php

declare(strict_types=1);

namespace Domain\Finance\Enums;

enum LedgerType: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
