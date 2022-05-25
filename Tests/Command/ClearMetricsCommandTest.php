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

        $expected = <<<EOL
Clearing storage from {$className}
<success>The storage was successfully cleared.</success>

EOL;

        $this->assertSame($expected, $tester->getDisplay());
    }
}
