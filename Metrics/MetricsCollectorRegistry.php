<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

/**
 * MetricsCollectorRegistry holds all registered metric collectors.
 */
final class MetricsCollectorRegistry
{
    /**
     * @var MetricsCollectorInterface[]
     */
    private array $collectors = [];

    public function registerMetricsCollector(MetricsCollectorInterface $collector): void
    {
        $this->collectors[] = $collector;
    }

    /**
     * @return MetricsCollectorInterface[]
     */
    public function getMetricsCollectors(): array
    {
        return $this->collectors;
    }
}
