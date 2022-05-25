<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\InMemoryFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\InMemory;

class InMemoryFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new InMemoryFactory();

        self::assertInstanceOf(InMemory::class, $factory->create([]));
    }

    public function testFactoryName(): void
    {
        $factory = new InMemoryFactory();

        self::assertSame('in_memory', $factory->getName());
    }
}
