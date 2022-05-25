<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Command;

use Artprima\PrometheusMetricsBundle\Command\ClearMetricsCommand;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\Adapter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClearMetricsCommandTest extends TestCase
{
    public function testClear()
    {
        $adapter = $this->createMock(Adapter::class);
        $adapter->expects($this->once())->method('wipeStorage');
        $className = get_class($adapter);

        $command = new ClearMetricsCommand($adapter);
        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($application->get('artprima:prometheus:metrics:clear'));
        $tester->execute([]);

        $this->assertStringContainsString("Clearing storage from $className", $tester->getDisplay());
        $this->assertStringContainsString("[OK] The storage was successfully cleared.", $tester->getDisplay());
    }
}
