<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Class AppMetrics.
 */
class AppMetrics implements MetricsGeneratorInterface
{
    private const STOPWATCH_CLASS = '\Symfony\Component\Stopwatch\Stopwatch';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var CollectorRegistry
     */
    private $collectionRegistry;

    public function init(string $namespace, CollectorRegistry $collectionRegistry)
    {
        if (class_exists(self::STOPWATCH_CLASS)) {
            $className = self::STOPWATCH_CLASS;
            $this->stopwatch = new $className();
            $this->stopwatch->start('execution_time');
        }
        $this->namespace = $namespace;
        $this->collectionRegistry = $collectionRegistry;
    }

    private function setInstance(string $value): void
    {
        $name = 'instance_name';
        try {
            // the trick with try/catch let's us setting the instance name only once
            $this->collectionRegistry->getGauge($this->namespace, $name);
        } catch (MetricNotFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $gauge = $this->collectionRegistry->registerGauge(
                $this->namespace,
                $name,
                'app instance name',
                ['instance']
            );
            $gauge->set(1, [$value]);
        }
    }

    private function incRequestsTotal(?string $method = null, ?string $route = null): void
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

    private function inc2xxResponsesTotal(?string $method = null, ?string $route = null): void
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

    private function inc4xxResponsesTotal(?string $method = null, ?string $route = null): void
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

    private function inc5xxResponsesTotal(?string $method = null, ?string $route = null): void
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

    private function setRequestDuration(float $duration, ?string $method = null, ?string $route = null): void
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

    public function collectRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $requestMethod = $request->getMethod();
        $requestRoute = $request->attributes->get('_route');

        // do not track "OPTIONS" requests
        if ('OPTIONS' === $requestMethod) {
            return;
        }

        $this->setInstance($request->server->get('HOSTNAME') ?? 'dev');
        $this->incRequestsTotal($requestMethod, $requestRoute);
    }

    public function collectResponse(PostResponseEvent $event)
    {
        $evt = $this->stopwatch ? $this->stopwatch->stop('execution_time') : null;
        $response = $event->getResponse();
        $request = $event->getRequest();

        $requestMethod = $request->getMethod();
        $requestRoute = $request->attributes->get('_route');

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->inc2xxResponsesTotal($requestMethod, $requestRoute);
        } elseif ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            $this->inc4xxResponsesTotal($requestMethod, $requestRoute);
        } elseif ($response->getStatusCode() >= 500) {
            $this->inc5xxResponsesTotal($requestMethod, $requestRoute);
        }

        if (null !== $evt) {
            $this->setRequestDuration($evt->getDuration() / 1000, $requestMethod, $requestRoute);
        }
    }
}
