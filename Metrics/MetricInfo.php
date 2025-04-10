<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\MetricInfoResolverCompilerPass;

/**
 * MetricInfo it's a model that defines the way of recording labels for the metrics.
 * Combined with MetricInfoResolverInterface can be extended to provide custom labels.
 *
 * @see MetricInfoResolverInterface
 * @see MetricInfoResolverCompilerPass
 */
final class MetricInfo
{
    private array $labelValues;

    public function __construct(array $labelValues = [])
    {
        $this->labelValues = $labelValues;
    }

    /**
     * Will return: ['GET-/api/v1/users', 'red', 'mobile-app'].
     *
     * @return array<string>
     */
    public function getLabelValues(): array
    {
        return $this->labelValues;
    }
}
