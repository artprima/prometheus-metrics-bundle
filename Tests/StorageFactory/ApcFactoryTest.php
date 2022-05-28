<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\ApcFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\APC;

class ApcFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new ApcFactory();

        self::assertInstanceOf(APC::class, $factory->create([]));
    }

    public function testFactoryName(): void
    {
        $factory = new ApcFactory();

        self::assertSame('apcu', $factory->getName());
    }
}
