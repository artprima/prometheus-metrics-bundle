<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\Storage;

use Artprima\PrometheusMetricsBundle\StorageFactory\StorageFactoryInterface;
use Prometheus\Storage\Adapter;

class DummyFactory implements StorageFactoryInterface
{
    public function getName(): string
    {
        return 'dummy';
    }

    public function create(array $options): Adapter
    {
        return new Dummy($options);
    }
}
