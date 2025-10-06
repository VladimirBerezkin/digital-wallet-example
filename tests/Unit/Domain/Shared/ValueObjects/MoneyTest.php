<?php

declare(strict_types=1);

use Domain\Shared\ValueObjects\Money;

describe('Money Value Object', function (): void {
    it('creates money from string with proper precision', function (): void {
        $money = new Money('100.50');

        expect($money->getAmount())->toBe('100.5000');
    });

    it('creates money from integer', function (): void {
        $money = new Money(100);

        expect($money->getAmount())->toBe('100.0000');
    });

    it('creates money from float', function (): void {
        $money = new Money(100.50);

        expect($money->getAmount())->toBe('100.5000');
    });

    it('adds two money values correctly', function (): void {
        $money1 = new Money('100.50');
        $money2 = new Money('50.25');

        $result = $money1->add($money2);

        expect($result->getAmount())->toBe('150.7500');
    });

    it('subtracts money correctly', function (): void {
        $money1 = new Money('100.50');
        $money2 = new Money('50.25');

        $result = $money1->subtract($money2);

        expect($result->getAmount())->toBe('50.2500');
    });

    it('multiplies money by string multiplier', function (): void {
        $money = new Money('100');

        $result = $money->multiply('0.015'); // 1.5% commission

        expect($result->getAmount())->toBe('1.5000');
    });

    it('multiplies money by numeric multiplier', function (): void {
        $money = new Money(100);

        $result = $money->multiply(0.015);

        expect($result->getAmount())->toBe('1.5000');
    });

    it('compares money values correctly', function (): void {
        $money1 = new Money('100.50');
        $money2 = new Money('50.25');

        expect($money1->greaterThan($money2))->toBeTrue();
        expect($money1->lessThan($money2))->toBeFalse();
    });

    it('checks equality correctly', function (): void {
        $money1 = new Money('100.50');
        $money2 = new Money(100.50);
        $money3 = new Money('100.5000');

        expect($money1->equals($money2))->toBeTrue();
        expect($money1->equals($money3))->toBeTrue();
        expect($money1->equals('100.50'))->toBeTrue();
    });

    it('formats money correctly', function (): void {
        $money = new Money('100.50');

        expect($money->format())->toBe('$100.50');
    });

    it('handles zero values', function (): void {
        $money = new Money('0');

        expect($money->getAmount())->toBe('0.0000');
    });

    it('handles large decimal values', function (): void {
        $money = new Money('999999.9999');

        expect($money->getAmount())->toBe('999999.9999');
    });

    it('can add string amounts', function (): void {
        $money = new Money('100.00');

        $result = $money->add('50.00');

        expect($result->getAmount())->toBe('150.0000');
    });

    it('can add integer amounts', function (): void {
        $money = new Money('100.00');

        $result = $money->add(50);

        expect($result->getAmount())->toBe('150.0000');
    });

    it('can add float amounts', function (): void {
        $money = new Money('100.00');

        $result = $money->add(50.25);

        expect($result->getAmount())->toBe('150.2500');
    });

    it('can subtract string amounts', function (): void {
        $money = new Money('100.00');

        $result = $money->subtract('50.00');

        expect($result->getAmount())->toBe('50.0000');
    });

    it('handles comparison with string amounts', function (): void {
        $money = new Money('100.00');

        expect($money->greaterThan('50.00'))->toBeTrue();
        expect($money->lessThan('150.00'))->toBeTrue();
    });

    it('handles comparison with numeric amounts', function (): void {
        $money = new Money('100.00');

        expect($money->greaterThan(50))->toBeTrue();
        expect($money->lessThan(150.50))->toBeTrue();
    });

    it('converts to float', function (): void {
        $money = new Money('100.50');

        expect($money->toFloat())->toBe(100.50);
    });

    it('converts to string', function (): void {
        $money = new Money('100.50');

        expect((string) $money)->toBe('100.5000');
    });

    it('throws exception for negative amounts', function (): void {
        new Money('-100.50');
    })->throws(InvalidArgumentException::class, 'Money amount cannot be negative');

    it('rounds to cents correctly', function (): void {
        // Test standard rounding (round half up)
        expect((new Money('0.044'))->roundToCents()->getAmount())->toBe('0.0400');
        expect((new Money('0.045'))->roundToCents()->getAmount())->toBe('0.0500');
        expect((new Money('0.046'))->roundToCents()->getAmount())->toBe('0.0500');

        // Test commission calculation scenario
        $transferAmount = new Money('3.00');
        $commission = $transferAmount->multiply('0.015')->roundToCents();
        expect($commission->getAmount())->toBe('0.0500');

        // Test total calculation
        $totalDebit = $transferAmount->add($commission);
        expect($totalDebit->getAmount())->toBe('3.0500');
    });

    it('handles the specific $10k transfer scenario correctly', function (): void {
        // Specific case: $10,000 starting balance, $3 transfer
        $startingBalance = new Money('10000.00');
        $transferAmount = new Money('3.00');

        // Calculate commission (1.5%) and round to cents
        $commission = $transferAmount->multiply('0.015')->roundToCents();

        // Verify commission is $0.05 (rounded from $0.045)
        expect($commission->getAmount())->toBe('0.0500');

        // Calculate total debit
        $totalDebit = $transferAmount->add($commission);
        expect($totalDebit->getAmount())->toBe('3.0500');

        // Calculate final balance
        $finalBalance = $startingBalance->subtract($totalDebit);
        expect($finalBalance->getAmount())->toBe('9996.9500');

        // Verify the math: $3.00 + $0.05 = $3.05, $10,000 - $3.05 = $9,996.95
        expect($transferAmount->add($commission)->getAmount())->toBe('3.0500');
        expect($startingBalance->subtract($transferAmount->add($commission))->getAmount())->toBe('9996.9500');
    });
});
