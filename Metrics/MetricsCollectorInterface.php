<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;

/**
 * MetricsCollectorInterface is a basic interface to be implemented by any metrics collector.
 */
interface MetricsCollectorInterface
{
    public function init(string $namespace, CollectorRegistry $collectionRegistry): void;
}
