<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\FactoryRegistry;
use Artprima\PrometheusMetricsBundle\StorageFactory\InMemoryFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\InMemory;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\Storage\Dummy;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\Storage\DummyFactory;

class FactoryRegistryTest extends TestCase
{
    public function testCreateFromEmptyValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new FactoryRegistry();
        $factory->create([]);
    }

    public function testCreateWithWrongInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new FactoryRegistry([new InMemoryFactory()]);
        $factory->create(['url' => 'redis']);
    }

    public function testCreate(): void
    {
        $factory = new FactoryRegistry([new InMemoryFactory()]);

        self::assertInstanceOf(InMemory::class, $factory->create(['url' => 'in_memory']));
    }

    public function testCreateWithOption(): void
    {
        $factory = new FactoryRegistry([new DummyFactory()]);
        $adapter = $factory->create([
            'url' => 'dummy://localhost:9999/test/?foo1=bar1',
            'foo2' => 'bar2',
        ]);

        self::assertInstanceOf(Dummy::class, $adapter);
        self::assertSame('localhost', $adapter->options['host']);
        self::assertSame(9999, $adapter->options['port']);
        self::assertSame('test', $adapter->options['path']);
        self::assertSame('bar1', $adapter->options['foo1']);
        self::assertSame('bar2', $adapter->options['foo2']);
    }

    public function testCreateWithWrongDsn(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new FactoryRegistry();
        $factory->create(['url' => 'http:///']);
    }
}
