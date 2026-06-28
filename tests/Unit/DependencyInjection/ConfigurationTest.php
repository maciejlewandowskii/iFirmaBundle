<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\DependencyInjection;

use maciejlewandowskii\iFirmaApi\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    public function testRequiredCredentialsAreProcessed(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [[
            'credentials' => [
                'username' => 'myuser',
                'invoice_key' => 'inv_key',
                'subscriber_key' => 'sub_key',
            ],
        ]]);

        $this->assertSame('myuser', $config['credentials']['username']);
        $this->assertSame('inv_key', $config['credentials']['invoice_key']);
        $this->assertSame('sub_key', $config['credentials']['subscriber_key']);
        $this->assertSame('', $config['credentials']['expense_key']);
    }

    public function testOptionalExpenseKeyCanBeSet(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [[
            'credentials' => [
                'username' => 'myuser',
                'invoice_key' => 'inv_key',
                'subscriber_key' => 'sub_key',
                'expense_key' => 'exp_key',
            ],
        ]]);

        $this->assertSame('exp_key', $config['credentials']['expense_key']);
    }
}
