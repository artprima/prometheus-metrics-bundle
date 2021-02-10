<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;

/**
 * MetricsCollectorInitTrait defines the init function that is in most cases the same for all the collectors.
 */
trait MetricsCollectorInitTrait
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var CollectorRegistry
     */
    protected $collectionRegistry;

    public function init(string $namespace, CollectorRegistry $collectionRegistry): void
    {
        $this->namespace = $namespace;
        $this->collectionRegistry = $collectionRegistry;
    }
}
