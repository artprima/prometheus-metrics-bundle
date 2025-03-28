<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricInfo;
use Artprima\PrometheusMetricsBundle\Metrics\MetricInfoResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class DummyMetricInfoResolver implements MetricInfoResolverInterface
{
    public function resolveData(Request $request, array $labelValues = []): MetricInfo
    {
        return new MetricInfo(
            [
                sprintf('%s %s', $request->getMethod(), $request->getPathInfo()),
                ...$labelValues, // Additional labels.
            ]
        );
    }
}
