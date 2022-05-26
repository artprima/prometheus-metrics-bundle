<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\StorageFactory;

use Prometheus\Storage\APCng;

class APCngFactory implements StorageFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options): APCng
    {
        return new APCng($options['prefix'] ?? APCng::PROMETHEUS_PREFIX);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'apcng';
    }
}
