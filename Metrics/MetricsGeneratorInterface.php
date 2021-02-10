<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

/**
 * MetricsGeneratorInterface is a deprecated basic interface that used to be implemented by any metrics collector.
 *
 * @deprecated in 1.8, use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface
 */
interface MetricsGeneratorInterface extends MetricsCollectorInterface
{
}
