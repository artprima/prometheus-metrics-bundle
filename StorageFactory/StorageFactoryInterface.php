<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;

interface StorageFactoryInterface
{
    /**
     * Gets the name of the adapter managed by this factory.
     */
    public function getName(): string;

    /**
     * Creates the adapter instance.
     */
    public function create(array $options): Adapter;
}
