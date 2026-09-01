<?php

namespace Tests\Unit\Core\Money;

use App\Core\Money\Currency;
use App\Core\Money\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_stores_canonical_amount_as_integer_irr(): void
    {
        $money = Money::fromIrr(2_500_000);

        $this->assertSame(2_500_000, $money->amount);
        $this->assertSame(Currency::IranianRial, $money->currency);
    }

    public function test_converts_exact_toman_and_thousand_toman_values_without_float_arithmetic(): void
    {
        $money = Money::fromToman(250_000);

        $this->assertSame(2_500_000, $money->amount);
        $this->assertSame(250_000, $money->toToman());
        $this->assertSame(250, $money->toThousandToman());
    }

    public function test_rejects_negative_amounts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromIrr(-1);
    }

    public function test_rejects_lossy_toman_conversion(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromIrr(11)->toToman();
    }
}
