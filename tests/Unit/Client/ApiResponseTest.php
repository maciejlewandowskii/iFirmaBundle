<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Client;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    // ── getInt ─────────────────────────────────────────────────────────────

    public function testGetIntReturnsIntValue(): void
    {
        $this->assertSame(42, (new ApiResponse(['n' => 42]))->getInt('n'));
    }

    public function testGetIntConvertsFloat(): void
    {
        $this->assertSame(3, (new ApiResponse(['n' => 3.9]))->getInt('n'));
    }

    public function testGetIntConvertsNumericString(): void
    {
        $this->assertSame(7, (new ApiResponse(['n' => '7']))->getInt('n'));
    }

    public function testGetIntConvertsBoolTrue(): void
    {
        $this->assertSame(1, (new ApiResponse(['n' => true]))->getInt('n'));
    }

    public function testGetIntConvertsBoolFalse(): void
    {
        $this->assertSame(0, (new ApiResponse(['n' => false]))->getInt('n'));
    }

    public function testGetIntMissingKeyReturnsDefault(): void
    {
        $this->assertSame(0, (new ApiResponse([]))->getInt('x'));
        $this->assertSame(99, (new ApiResponse([]))->getInt('x', 99));
    }

    // ── getString ──────────────────────────────────────────────────────────

    public function testGetStringReturnsStringValue(): void
    {
        $this->assertSame('hello', (new ApiResponse(['s' => 'hello']))->getString('s'));
    }

    public function testGetStringConvertsInt(): void
    {
        $this->assertSame('5', (new ApiResponse(['s' => 5]))->getString('s'));
    }

    public function testGetStringConvertsFloat(): void
    {
        $this->assertSame('3.14', (new ApiResponse(['s' => 3.14]))->getString('s'));
    }

    public function testGetStringConvertsBoolTrue(): void
    {
        $this->assertSame('1', (new ApiResponse(['s' => true]))->getString('s'));
    }

    public function testGetStringConvertsBoolFalse(): void
    {
        $this->assertSame('0', (new ApiResponse(['s' => false]))->getString('s'));
    }

    public function testGetStringMissingKeyReturnsDefault(): void
    {
        $this->assertSame('', (new ApiResponse([]))->getString('x'));
        $this->assertSame('fallback', (new ApiResponse([]))->getString('x', 'fallback'));
    }

    // ── getFloat ───────────────────────────────────────────────────────────

    public function testGetFloatReturnsFloatValue(): void
    {
        $this->assertSame(1.5, (new ApiResponse(['f' => 1.5]))->getFloat('f'));
    }

    public function testGetFloatConvertsInt(): void
    {
        $this->assertSame(3.0, (new ApiResponse(['f' => 3]))->getFloat('f'));
    }

    public function testGetFloatConvertsString(): void
    {
        $this->assertSame(2.5, (new ApiResponse(['f' => '2.5']))->getFloat('f'));
    }

    public function testGetFloatConvertsBoolTrue(): void
    {
        $this->assertSame(1.0, (new ApiResponse(['f' => true]))->getFloat('f'));
    }

    public function testGetFloatConvertsBoolFalse(): void
    {
        $this->assertSame(0.0, (new ApiResponse(['f' => false]))->getFloat('f'));
    }

    public function testGetFloatMissingKeyReturnsDefault(): void
    {
        $this->assertSame(0.0, (new ApiResponse([]))->getFloat('x'));
        $this->assertSame(9.9, (new ApiResponse([]))->getFloat('x', 9.9));
    }

    // ── getBool ────────────────────────────────────────────────────────────

    public function testGetBoolReturnsBoolTrue(): void
    {
        $this->assertTrue((new ApiResponse(['b' => true]))->getBool('b'));
    }

    public function testGetBoolReturnsBoolFalse(): void
    {
        $this->assertFalse((new ApiResponse(['b' => false]))->getBool('b'));
    }

    public function testGetBoolConvertsIntZeroToFalse(): void
    {
        $this->assertFalse((new ApiResponse(['b' => 0]))->getBool('b'));
    }

    public function testGetBoolConvertsNonZeroIntToTrue(): void
    {
        $this->assertTrue((new ApiResponse(['b' => 1]))->getBool('b'));
    }

    public function testGetBoolConvertsEmptyStringToFalse(): void
    {
        $this->assertFalse((new ApiResponse(['b' => '']))->getBool('b'));
    }

    public function testGetBoolConvertsZeroStringToFalse(): void
    {
        $this->assertFalse((new ApiResponse(['b' => '0']))->getBool('b'));
    }

    public function testGetBoolConvertsNonEmptyStringToTrue(): void
    {
        $this->assertTrue((new ApiResponse(['b' => '1']))->getBool('b'));
    }

    public function testGetBoolConvertsZeroFloatToFalse(): void
    {
        $this->assertFalse((new ApiResponse(['b' => 0.0]))->getBool('b'));
    }

    public function testGetBoolMissingKeyReturnsDefault(): void
    {
        $this->assertFalse((new ApiResponse([]))->getBool('x'));
        $this->assertTrue((new ApiResponse([]))->getBool('x', true));
    }

    // ── getNullableString ──────────────────────────────────────────────────

    public function testGetNullableStringReturnsString(): void
    {
        $this->assertSame('foo', (new ApiResponse(['s' => 'foo']))->getNullableString('s'));
    }

    public function testGetNullableStringConvertsInt(): void
    {
        $this->assertSame('42', (new ApiResponse(['s' => 42]))->getNullableString('s'));
    }

    public function testGetNullableStringConvertsFloat(): void
    {
        $this->assertSame('1.5', (new ApiResponse(['s' => 1.5]))->getNullableString('s'));
    }

    public function testGetNullableStringConvertsBoolTrue(): void
    {
        $this->assertSame('1', (new ApiResponse(['s' => true]))->getNullableString('s'));
    }

    public function testGetNullableStringConvertsBoolFalse(): void
    {
        $this->assertSame('0', (new ApiResponse(['s' => false]))->getNullableString('s'));
    }

    public function testGetNullableStringReturnsNullForNullValue(): void
    {
        $this->assertNull((new ApiResponse(['s' => null]))->getNullableString('s'));
    }

    public function testGetNullableStringReturnsNullForAbsentKey(): void
    {
        $this->assertNull((new ApiResponse([]))->getNullableString('missing'));
    }

    public function testGetNullableStringReturnsNullForUnknownType(): void
    {
        $this->assertNull((new ApiResponse(['s' => ['array']]))->getNullableString('s'));
    }

    // ── getResponseList ────────────────────────────────────────────────────

    public function testGetResponseListReturnsListOfApiResponses(): void
    {
        $response = new ApiResponse([
            'items' => [
                ['name' => 'Alice', 'age' => 30],
                ['name' => 'Bob',   'age' => 25],
            ],
        ]);

        $list = $response->getResponseList('items');

        $this->assertCount(2, $list);
        $this->assertSame('Alice', $list[0]->getString('name'));
        $this->assertSame(25, $list[1]->getInt('age'));
    }

    public function testGetResponseListSkipsNonArrayItems(): void
    {
        $response = new ApiResponse(['items' => ['string', 42, ['key' => 'val']]]);
        $list = $response->getResponseList('items');

        $this->assertCount(1, $list);
        $this->assertSame('val', $list[0]->getString('key'));
    }

    public function testGetResponseListReturnsEmptyForMissingKey(): void
    {
        $this->assertSame([], (new ApiResponse([]))->getResponseList('none'));
    }

    public function testGetResponseListReturnsEmptyForNonArrayValue(): void
    {
        $this->assertSame([], (new ApiResponse(['items' => 'not-an-array']))->getResponseList('items'));
    }

    // ── getFirstResponse ───────────────────────────────────────────────────

    public function testGetFirstResponseReturnsFirstItem(): void
    {
        $response = new ApiResponse(['list' => [['id' => 1], ['id' => 2]]]);
        $first = $response->getFirstResponse('list');

        $this->assertNotNull($first);
        $this->assertSame(1, $first->getInt('id'));
    }

    public function testGetFirstResponseReturnsNullForEmptyList(): void
    {
        $this->assertNull((new ApiResponse(['list' => []]))->getFirstResponse('list'));
    }

    public function testGetFirstResponseReturnsNullForMissingKey(): void
    {
        $this->assertNull((new ApiResponse([]))->getFirstResponse('list'));
    }

    // ── has / isEmpty / toArray ────────────────────────────────────────────

    public function testHasReturnsTrueForPresentKey(): void
    {
        $this->assertTrue((new ApiResponse(['key' => 'val']))->has('key'));
    }

    public function testHasReturnsFalseForAbsentKey(): void
    {
        $this->assertFalse((new ApiResponse([]))->has('key'));
    }

    public function testHasReturnsTrueForNullValue(): void
    {
        $this->assertTrue((new ApiResponse(['key' => null]))->has('key'));
    }

    public function testIsEmptyReturnsTrueForEmptyData(): void
    {
        $this->assertTrue((new ApiResponse([]))->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyData(): void
    {
        $this->assertFalse((new ApiResponse(['k' => 'v']))->isEmpty());
    }

    public function testToArrayReturnsRawData(): void
    {
        $data = ['Kod' => 0, 'Informacja' => 'ok'];
        $this->assertSame($data, (new ApiResponse($data))->toArray());
    }
}
