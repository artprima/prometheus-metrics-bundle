<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Controller;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;

/**
 * Class MetricsController.
 */
class MetricsController
{
    /**
     * @var AppMetrics
     */
    private $metrics;

    public function __construct(Renderer $metricsRenderer)
    {
        $this->metrics = $metricsRenderer;
    }

    public function prometheus()
    {
        return $this->metrics->renderResponse();
    }
}
