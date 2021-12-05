<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Controller;

use Artprima\PrometheusMetricsBundle\Metrics\Renderer;

/**
 * Class MetricsController.
 */
class MetricsController
{
    private Renderer $renderer;

    public function __construct(Renderer $metricsRenderer)
    {
        $this->renderer = $metricsRenderer;
    }

    public function prometheus()
    {
        return $this->renderer->renderResponse();
    }
}
