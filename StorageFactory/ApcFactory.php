<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\Adapter;
use Prometheus\Storage\APC;

class ApcFactory implements StorageFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return APC
     */
    public function create(array $options): Adapter
    {
        return new APC($options['prefix'] ?? APC::PROMETHEUS_PREFIX);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'apcu';
    }
}
