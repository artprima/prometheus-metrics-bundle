<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AppMetrics.
 */
class AppMetrics
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var CollectorRegistry
     */
    private $collectionRegistry;

    public function __construct(string $namespace, CollectorRegistry $collectionRegistry)
    {
        $this->namespace = $namespace;
        $this->collectionRegistry = $collectionRegistry;
    }

    public function setInstance(string $value): void
    {
        $name = 'instance_name';
        try {
            // the trick with try/catch let's us setting the instance name only once
            $this->collectionRegistry->getGauge($this->namespace, $name);
        } catch (MetricNotFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $gauge = $this->collectionRegistry->registerGauge($this->namespace, $name, 'app instance name', ['instance']);
            $gauge->set(1, [$value]);
        }
    }

    public function incRequestsTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_requests_total',
            'total request count',
            ['action']
        );

        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    public function inc2xxResponsesTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_2xx_responses_total',
            'total 2xx response count',
            ['action']
        );
        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    public function inc4xxResponsesTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_4xx_responses_total',
            'total 4xx response count',
            ['action']
        );
        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    public function inc5xxResponsesTotal(?string $method = null, ?string $route = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_5xx_responses_total',
            'total 5xx response count',
            ['action']
        );
        $counter->inc(['all']);

        if (null !== $method && null !== $route) {
            $counter->inc([sprintf('%s-%s', $method, $route)]);
        }
    }

    public function setRequestDuration(float $duration, ?string $method = null, ?string $route = null): void
    {
        $histogram = $this->collectionRegistry->getOrRegisterHistogram(
            $this->namespace,
            'request_durations_histogram_seconds',
            'request durations in seconds',
            ['action']
        );
        $histogram->observe($duration, ['all']);

        if (null !== $method && null !== $route) {
            $histogram->observe($duration, [sprintf('%s-%s', $method, $route)]);
        }
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
