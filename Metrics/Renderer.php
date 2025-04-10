<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renderer class provides a functionality to render the metrics for the given collector registry.
 */
class Renderer
{
    public function __construct(private readonly CollectorRegistry $collectionRegistry)
    {
    }

    public function render(): string
    {
        return (new RenderTextFormat())->render($this->collectionRegistry->getMetricFamilySamples());
    }

    public function renderResponse(): Response
    {
        return new Response($this->render(), 200, ['Content-type' => RenderTextFormat::MIME_TYPE]);
    }
}
