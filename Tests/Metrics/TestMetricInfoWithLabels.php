<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\MetricInfo;

class TestMetricInfoWithLabels extends MetricInfo
{
    public function getLabelValues(): array
    {
        $values = [sprintf('%s %s', $this->getRequestMethod(), $this->getRequestRoute())];

        return array_merge($values, $this->getAdditionalLabelValues());
    }
}
