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
    public function __construct(private readonly Renderer $renderer)
    {
    }

    public function prometheus(): Response
    {
        return $this->renderer->renderResponse();
    }
}
