<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricInfo;

class TestMetricInfo extends MetricInfo
{
    public function getLabelValues(): array
    {
        return [sprintf('%s %s', $this->getRequestMethod(), $this->getRequestRoute())];
    }
}
