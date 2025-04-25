<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\APCngFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\APCng;

class APCngFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        if (!\extension_loaded('apcu') || !apcu_enabled()) {
            self::markTestSkipped('Cannot find the "apcu" extension or not enabled');
        }

        $factory = new APCngFactory();

        self::assertInstanceOf(APCng::class, $factory->create([]));
    }

    public function testFactoryName(): void
    {
        $factory = new APCngFactory();

        self::assertSame('apcng', $factory->getName());
    }
}
