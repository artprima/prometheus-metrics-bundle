<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorRegistry;
use PHPUnit\Framework\TestCase;

class MetricsGeneratorsRegistryTest extends TestCase
{
    public function testRegisterAndGetMetricsGenerators(): void
    {
        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator2 = $this->createMock(MetricsGeneratorInterface::class);

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);
        $registry->registerMetricsGenerator($generator2);

        self::assertEquals([$generator1, $generator2], $registry->getMetricsGenerators());
    }
}
