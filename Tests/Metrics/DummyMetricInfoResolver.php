<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricInfo;
use Artprima\PrometheusMetricsBundle\Metrics\MetricInfoResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class DummyMetricInfoResolver implements MetricInfoResolverInterface
{
    public function resolveData(Request $request): MetricInfo
    {
        return new TestMetricInfo(
            $request->getMethod(),
            $request->getPathInfo()
        );
    }
}
