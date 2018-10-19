<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Controller;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;

/**
 * Class MetricsController.
 */
class MetricsController
{
    /**
     * @var AppMetrics
     */
    private $metrics;

    public function __construct(AppMetrics $metrics) {
        $this->metrics = $metrics;
    }

    public function prometheus()
    {
        return $this->metrics->renderResponse();
    }
}
