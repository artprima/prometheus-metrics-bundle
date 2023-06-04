<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;

class InMemoryFactory implements StorageFactoryInterface
{
    /**
     * @return InMemory
     */
    public function create(array $options): Adapter
    {
        return new InMemory();
    }

    public function getName(): string
    {
        return 'in_memory';
    }
}
