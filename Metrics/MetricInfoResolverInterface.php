<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpFoundation\Request;

interface MetricInfoResolverInterface
{
    public function resolveData(Request $request): MetricInfo;
}
