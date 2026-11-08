<?php

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

class Isbn
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(str_replace(['-', ' '], '', trim($value)));

        if (! self::isValidIsbn10($normalized) && ! self::isValidIsbn13($normalized)) {
            throw new InvalidArgumentException("Format ISBN tidak valid: {$value}");
        }

        $this->value = $normalized;
    }

    public static function isValid(string $value): bool
    {
        try {
            new self($value);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isIsbn13(): bool
    {
        return strlen($this->value) === 13;
    }

    public function equals(Isbn $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function isValidIsbn10(string $isbn): bool
    {
        if (preg_match('/^\d{9}[\dX]$/', $isbn) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (str_split($isbn) as $index => $character) {
            $sum += (10 - $index) * ($character === 'X' ? 10 : (int) $character);
        }

        return $sum % 11 === 0;
    }

    private static function isValidIsbn13(string $isbn): bool
    {
        if (preg_match('/^97[89]\d{10}$/', $isbn) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (str_split($isbn) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }
}
