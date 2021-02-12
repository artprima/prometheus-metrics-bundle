<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\Exception\MetricNotFoundException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class AppMetrics is an implementation of basic metrics collector that is turned on by default.
 *
 * Collected metrics:
 * - requests (per method and route)
 * - responses (per method, route and response type)
 * - request duration histogram (per method and route)
 */
class AppMetrics implements MetricsCollectorInterface
{
    use MetricsCollectorInitTrait;

    private const STOPWATCH_CLASS = '\Symfony\Component\Stopwatch\Stopwatch';

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function collectRequest(RequestEvent $event): void
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

    public function collectResponse(TerminateEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $requestMethod = $request->getMethod();
        $requestRoute = $request->attributes->get('_route');
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            $this->incResponsesTotal('2xx', $requestMethod, $requestRoute, $statusCode);
        } elseif ($statusCode >= 300 && $statusCode < 400) {
            $this->incResponsesTotal('3xx', $requestMethod, $requestRoute, $statusCode);
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            $this->incResponsesTotal('4xx', $requestMethod, $requestRoute, $statusCode);
        } elseif ($statusCode >= 500) {
            $this->incResponsesTotal('5xx', $requestMethod, $requestRoute, $statusCode);
        }

        if ($this->stopwatch && $this->stopwatch->isStarted('execution_time')) {
            $evt = $this->stopwatch->stop('execution_time');
            if (null !== $evt) {
                $this->setRequestDuration($evt->getDuration() / 1000, $requestMethod, $requestRoute);
            }
        }
    }

    public function collectStart(RequestEvent $event): void
    {
        // do not track "OPTIONS" requests
        if ($event->getRequest()->isMethod('OPTIONS')) {
            return;
        }

        if (class_exists(self::STOPWATCH_CLASS)) {
            $className = self::STOPWATCH_CLASS;
            $this->stopwatch = new $className();
            $this->stopwatch->start('execution_time');
        }
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

    private function incResponsesTotal(string $type, ?string $method = null, ?string $route = null, ?int $status = null): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            sprintf('http_%s_responses_total', $type),
            sprintf('total %s response count', $type),
            ['action', 'status']
        );
        $counter->inc(['all', 'all']);

        if (null !== $method && null !== $route && null !== $status) {
            $counter->inc([sprintf('%s-%s', $method, $route), $status]);
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
}
