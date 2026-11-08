<?php

namespace Modules\Library\Circulation\ValueObjects;

use Carbon\CarbonInterface;
use Modules\Shared\ValueObjects\Money;

/**
 * Denda keterlambatan: jumlah hari terlambat x tarif denda per hari.
 */
class Fine
{
    private function __construct(
        private int $daysLate,
        private Money $amount,
    ) {}

    public static function calculate(CarbonInterface $dueAt, CarbonInterface $returnedAt, Money $finePerDay): self
    {
        $daysLate = max(0, (int) $dueAt->copy()->startOfDay()->diffInDays($returnedAt->copy()->startOfDay(), false));

        return new self($daysLate, $finePerDay->multiply($daysLate));
    }

    public function getDaysLate(): int
    {
        return $this->daysLate;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function isCharged(): bool
    {
        return $this->daysLate > 0;
    }

    public function __toString(): string
    {
        return 'Rp '.number_format($this->amount->getAmount(), 0, ',', '.');
    }
}
