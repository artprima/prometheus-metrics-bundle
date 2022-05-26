<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\StorageFactory;

use Artprima\PrometheusMetricsBundle\StorageFactory\ApcFactory;
use PHPUnit\Framework\TestCase;

class ApcFactoryTest extends TestCase
{
    public function testFactoryName(): void
    {
        $factory = new ApcFactory();

        self::assertSame('apcu', $factory->getName());
    }
}
