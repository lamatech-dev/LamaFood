<?php

namespace App\Core\Money;

use InvalidArgumentException;
use OverflowException;

final readonly class Money
{
    private function __construct(
        public int $amount,
        public Currency $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function fromIrr(int $amount): self
    {
        return new self($amount, Currency::IranianRial);
    }

    public static function fromToman(int $amount): self
    {
        if ($amount > intdiv(PHP_INT_MAX, 10)) {
            throw new OverflowException('Toman amount is too large to convert to IRR.');
        }

        return self::fromIrr($amount * 10);
    }

    public function toToman(): int
    {
        if ($this->amount % 10 !== 0) {
            throw new InvalidArgumentException('IRR amount is not exactly representable in toman.');
        }

        return intdiv($this->amount, 10);
    }

    public function toThousandToman(): int
    {
        $toman = $this->toToman();

        if ($toman % 1000 !== 0) {
            throw new InvalidArgumentException('Toman amount is not exactly representable in thousand toman.');
        }

        return intdiv($toman, 1000);
    }
}
