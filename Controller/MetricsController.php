<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Controller;

use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use Symfony\Component\HttpFoundation\Response;

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

    public function prometheus(): Response
    {
        return $this->renderer->renderResponse();
    }
}
