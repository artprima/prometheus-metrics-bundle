<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use PHPUnit\Framework\TestCase;

class MetricsGeneratorsRegistryTest extends TestCase
{
    public function testRegisterAndGetMetricsGenerators(): void
    {
        $generator1 = $this->createMock(MetricsCollectorInterface::class);
        $generator2 = $this->createMock(MetricsCollectorInterface::class);

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($generator1);
        $registry->registerMetricsCollector($generator2);

        self::assertEquals([$generator1, $generator2], $registry->getMetricsCollectors());
    }
}
