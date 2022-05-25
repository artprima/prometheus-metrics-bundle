<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\RedisFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\Redis;

class RedisFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new RedisFactory();

        self::assertInstanceOf(Redis::class, $factory->create([]));
    }

    public function testFullOptions(): void
    {
        $factory = new RedisFactory();

        self::assertInstanceOf(Redis::class, $factory->create([
            'pass' => '',
            'path' => 1,
            'timeout' => 0.1,
            'read_timeout' => '10',
            'persistent_connections' => false,
            'prefix' => 'PROMETHEUS_',
        ]));
    }

    public function testFactoryName(): void
    {
        $factory = new RedisFactory();

        self::assertSame('redis', $factory->getName());
    }
}
