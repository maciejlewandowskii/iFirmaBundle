<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Serializer;

use maciejlewandowskii\iFirmaApi\Serializer\PascalCaseNameConverter;
use PHPUnit\Framework\TestCase;

final class PascalCaseNameConverterTest extends TestCase
{
    private PascalCaseNameConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new PascalCaseNameConverter();
    }

    public function testNormalizeUppercasesFirstLetter(): void
    {
        $this->assertSame('InvoiceNumber', $this->converter->normalize('invoiceNumber'));
    }

    public function testNormalizeAlreadyPascalCasePassesThrough(): void
    {
        $this->assertSame('Nazwa', $this->converter->normalize('Nazwa'));
    }

    public function testNormalizeSingleWordLowercaseGetsCapitalized(): void
    {
        $this->assertSame('Name', $this->converter->normalize('name'));
    }

    public function testDenormalizeLowercasesFirstLetter(): void
    {
        $this->assertSame('invoiceNumber', $this->converter->denormalize('InvoiceNumber'));
    }

    public function testDenormalizeAlreadyCamelCasePassesThrough(): void
    {
        $this->assertSame('nazwa', $this->converter->denormalize('Nazwa'));
    }

    public function testNormalizeThenDenormalizeIsRoundTrip(): void
    {
        $original = 'kodPocztowy';
        $this->assertSame($original, $this->converter->denormalize($this->converter->normalize($original)));
    }
}
