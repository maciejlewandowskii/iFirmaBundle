<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Enum;

use maciejlewandowskii\iFirmaApi\Enum\FlatRateTax;
use maciejlewandowskii\iFirmaApi\Enum\VatRate;
use PHPUnit\Framework\TestCase;

final class VatRateTest extends TestCase
{
    public function testToFloatReturnsFloatForNumericRates(): void
    {
        $this->assertSame(0.0, VatRate::Zero->toFloat());
        $this->assertSame(0.05, VatRate::Five->toFloat());
        $this->assertSame(0.08, VatRate::Eight->toFloat());
        $this->assertSame(0.23, VatRate::TwentyThree->toFloat());
    }

    public function testToFloatReturnsNullForExempt(): void
    {
        $this->assertNull(VatRate::Exempt->toFloat());
    }

    public function testExemptBackingValueIsStringNull(): void
    {
        $this->assertSame('null', VatRate::Exempt->value);
    }

    public function testFlatRateTaxToFloatConvertsBackingValue(): void
    {
        $this->assertSame(0.03, FlatRateTax::ThreePercent->toFloat());
        $this->assertSame(0.17, FlatRateTax::SeventeenPercent->toFloat());
        $this->assertSame(0.15, FlatRateTax::FifteenPercent->toFloat());
    }
}
