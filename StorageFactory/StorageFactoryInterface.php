<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;

/**
 * StorageFactoryInterface provides a way to create instance of @Prometheus\Storage\Adapter.
 * A storage factory should manage options type, and default values.
 */
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
