<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\DependencyInjection;

use Exception;
use maciejlewandowskii\iFirmaApi\DependencyInjection\IFirmaApiExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IFirmaApiExtensionTest extends TestCase
{
    /** @throws Exception */
    public function testLoadSetsCredentialParameters(): void
    {
        $extension = new IFirmaApiExtension();
        $container = new ContainerBuilder();

        $extension->load([[
            'credentials' => [
                'username' => 'testuser',
                'invoice_key' => 'invoice_abc',
                'subscriber_key' => 'sub_xyz',
                'expense_key' => 'exp_123',
            ],
        ]], $container);

        $this->assertSame('testuser', $container->getParameter('ifirma_api.credentials.username'));
        $this->assertSame('invoice_abc', $container->getParameter('ifirma_api.credentials.invoice_key'));
        $this->assertSame('sub_xyz', $container->getParameter('ifirma_api.credentials.subscriber_key'));
        $this->assertSame('exp_123', $container->getParameter('ifirma_api.credentials.expense_key'));
    }

    /** @throws Exception */
    public function testLoadWithDefaultExpenseKey(): void
    {
        $extension = new IFirmaApiExtension();
        $container = new ContainerBuilder();

        $extension->load([[
            'credentials' => [
                'username' => 'testuser',
                'invoice_key' => 'invoice_abc',
                'subscriber_key' => 'sub_xyz',
            ],
        ]], $container);

        $this->assertSame('', $container->getParameter('ifirma_api.credentials.expense_key'));
    }
}
