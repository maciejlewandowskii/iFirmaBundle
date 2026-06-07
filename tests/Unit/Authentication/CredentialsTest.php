<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Authentication;

use LogicException;
use maciejlewandowskii\iFirmaApi\Authentication\Credentials;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use PHPUnit\Framework\TestCase;

final class CredentialsTest extends TestCase
{
    private Credentials $credentials;

    protected function setUp(): void
    {
        $this->credentials = new Credentials(
            username: 'user@example.com',
            invoiceKey: 'aaaa1111bbbb2222',
            subscriberKey: 'cccc3333dddd4444',
            expenseKey: 'eeee5555ffff6666',
            accountKey: '11112222aaaabbbb',
        );
    }

    public function testGetUsernameReturnsUsername(): void
    {
        $this->assertSame('user@example.com', $this->credentials->getUsername());
    }

    public function testGetKeyForInvoiceType(): void
    {
        $this->assertSame('aaaa1111bbbb2222', $this->credentials->getKeyForType(AuthKeyType::Invoice));
    }

    public function testGetKeyForSubscriberType(): void
    {
        $this->assertSame('cccc3333dddd4444', $this->credentials->getKeyForType(AuthKeyType::Subscriber));
    }

    public function testGetKeyForExpenseType(): void
    {
        $this->assertSame('eeee5555ffff6666', $this->credentials->getKeyForType(AuthKeyType::Expense));
    }

    public function testGetKeyForAccountType(): void
    {
        $this->assertSame('11112222aaaabbbb', $this->credentials->getKeyForType(AuthKeyType::Account));
    }

    public function testGetKeyThrowsLogicExceptionWhenExpenseKeyMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Expense key not configured/');

        $credentials = new Credentials('user', 'aaaa', 'bbbb');
        $credentials->getKeyForType(AuthKeyType::Expense);
    }

    public function testGetKeyThrowsLogicExceptionWhenAccountKeyMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Account key not configured/');

        $credentials = new Credentials('user', 'aaaa', 'bbbb');
        $credentials->getKeyForType(AuthKeyType::Account);
    }
}
