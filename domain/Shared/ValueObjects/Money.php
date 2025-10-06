<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Money Value Object
 *
 * Represents monetary amounts with precision using BC Math.
 * Internally stores as string to avoid float precision issues.
 *
 * @property-read string $amount The precise amount as a string
 */
final readonly class Money
{
    private string $amount;

    /**
     * Create a new Money instance.
     *
     * @param  string|int|float  $amount  The amount (floats are converted safely)
     *
     * @throws InvalidArgumentException If amount is negative
     */
    public function __construct(string|int|float $amount)
    {
        // Convert to string for BC Math precision
        // BC Math handles string representations to avoid float precision issues
        $stringAmount = (string) $amount;

        // Validate non-negative (optional - remove if you need negative amounts)
        if (bccomp($stringAmount, '0', 4) < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }

        // Store with 4 decimal precision
        $this->amount = bcadd($stringAmount, '0', 4);
    }

    /**
     * Convert to string representation.
     */
    public function __toString(): string
    {
        return $this->amount;
    }

    /**
     * Add another amount to this Money.
     */
    public function add(self|string|int|float $other): self
    {
        $otherAmount = $other instanceof self ? $other->amount : (string) $other;

        return new self(bcadd($this->amount, $otherAmount, 4));
    }

    /**
     * Subtract another amount from this Money.
     */
    public function subtract(self|string|int|float $other): self
    {
        $otherAmount = $other instanceof self ? $other->amount : (string) $other;

        return new self(bcsub($this->amount, $otherAmount, 4));
    }

    /**
     * Multiply this Money by a multiplier.
     */
    public function multiply(string|int|float $multiplier): self
    {
        return new self(bcmul($this->amount, (string) $multiplier, 4));
    }

    /**
     * Check if this Money is greater than another amount.
     */
    public function greaterThan(self|string|int|float $other): bool
    {
        $otherAmount = $other instanceof self ? $other->amount : (string) $other;

        return bccomp($this->amount, $otherAmount, 4) > 0;
    }

    /**
     * Check if this Money is less than another amount.
     */
    public function lessThan(self|string|int|float $other): bool
    {
        $otherAmount = $other instanceof self ? $other->amount : (string) $other;

        return bccomp($this->amount, $otherAmount, 4) < 0;
    }

    /**
     * Check if this Money equals another amount.
     */
    public function equals(self|string|int|float $other): bool
    {
        $otherAmount = $other instanceof self ? $other->amount : (string) $other;

        return bccomp($this->amount, $otherAmount, 4) === 0;
    }

    /**
     * Get the amount as a string with 4 decimal precision.
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * Get the amount as a float (use with caution for display only).
     */
    public function toFloat(): float
    {
        return (float) $this->amount;
    }

    /**
     * Format the money for display.
     */
    public function format(): string
    {
        return '$'.number_format((float) $this->amount, 2);
    }

    /**
     * Round the money to 2 decimal places using standard rounding rules.
     * This ensures consistent display formatting across the application.
     */
    public function roundToCents(): self
    {
        // Round to 2 decimal places using standard rounding (round half up)
        $rounded = round((float) $this->amount, 2);

        return new self(number_format($rounded, 2, '.', ''));
    }
}
