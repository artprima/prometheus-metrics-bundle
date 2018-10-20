<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

class Renderer
{
    /**
     * @var CollectorRegistry
     */
    private $collectionRegistry;

    public function __construct(CollectorRegistry $collectionRegistry)
    {
        $this->collectionRegistry = $collectionRegistry;
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