<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\Exception\MetricNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class AppMetrics is an implementation of basic metrics collector that is turned on by default.
 *
 * Collected metrics:
 * - requests (per method and route)
 * - responses (per method, route and response type)
 * - request duration histogram (per method and route)
 */
class AppMetrics implements PreRequestMetricsCollectorInterface, RequestMetricsCollectorInterface, TerminateMetricsCollectorInterface
{
    use MetricsCollectorInitTrait;

    private float $startedAt = 0;

    private ?MetricInfoResolverInterface $metricInfoResolver = null;
    private ?LabelResolver $labelResolver = null;

    public function setMetricInfoResolver(MetricInfoResolverInterface $metricInfoResolver): void
    {
        $this->metricInfoResolver = $metricInfoResolver;
    }

    public function setLabelResolver(LabelResolver $labelResolver): void
    {
        $this->labelResolver = $labelResolver;
    }

    public function collectRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $metricInfo = $this->resolveMetricInfo($request);
        $requestMethod = $metricInfo->getRequestMethod();

        // do not track "OPTIONS" requests
        if ('OPTIONS' === $requestMethod) {
            return;
        }

        $this->setInstance($request->server->get('HOSTNAME') ?? 'dev');
        $this->incRequestsTotal($metricInfo);
    }

    public function collectResponse(TerminateEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $metricInfo = $this->resolveMetricInfo($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->incResponsesTotal('2xx', $metricInfo);
        } elseif ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
            $this->incResponsesTotal('3xx', $metricInfo);
        } elseif ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            $this->incResponsesTotal('4xx', $metricInfo);
        } elseif ($response->getStatusCode() >= 500) {
            $this->incResponsesTotal('5xx', $metricInfo);
        }

        $this->setRequestDuration(microtime(true) - $this->startedAt, $metricInfo);
    }

    public function collectStart(RequestEvent $event): void
    {
        // do not track "OPTIONS" requests
        if ($event->getRequest()->isMethod('OPTIONS')) {
            return;
        }

        $this->startedAt = microtime(true);
    }

    private function setInstance(string $value): void
    {
        $name = 'instance_name';
        try {
            // the trick with try/catch lets us setting the instance name only once
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

    private function incRequestsTotal(MetricInfo $metricInfo): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            'http_requests_total',
            'total request count',
            $this->getResolvedLabels()
        );

        $counter->inc($this->getAllLabelValues());

        if ($this->isMetricInfoValid($metricInfo)) {
            $counter->inc($metricInfo->getLabelValues());
        }
    }

    private function incResponsesTotal(string $type, MetricInfo $metricInfo): void
    {
        $counter = $this->collectionRegistry->getOrRegisterCounter(
            $this->namespace,
            sprintf('http_%s_responses_total', $type),
            sprintf('total %s response count', $type),
            $this->getResolvedLabels()
        );

        $counter->inc($this->getAllLabelValues());

        if ($this->isMetricInfoValid($metricInfo)) {
            $counter->inc($metricInfo->getLabelValues());
        }
    }

    private function setRequestDuration(float $duration, MetricInfo $metricInfo): void
    {
        $histogram = $this->collectionRegistry->getOrRegisterHistogram(
            $this->namespace,
            'request_durations_histogram_seconds',
            'request durations in seconds',
            $this->getResolvedLabels()
        );

        $histogram->observe($duration, $this->getAllLabelValues());

        if ($this->isMetricInfoValid($metricInfo)) {
            $histogram->observe($duration, $metricInfo->getLabelValues());
        }
    }

    private function isMetricInfoValid(MetricInfo $metricInfo): bool
    {
        if (null !== $metricInfo->getRequestMethod() && null !== $metricInfo->getRequestRoute()) {
            return true;
        }

        return false;
    }

    private function resolveMetricInfo(Request $request): MetricInfo
    {
        $labelValues = $this->getResolvedLabelValues($request);

        if (null === $this->metricInfoResolver) {
            return new MetricInfo($request->getMethod(), $request->attributes->get('_route'), $labelValues);
        }

        return $this->metricInfoResolver->resolveData($request, $labelValues);
    }

    public function getResolvedLabels(): array
    {
        if ($this->labelResolver) {
            return array_merge(['action'], $this->labelResolver->getLabelNames());
        }

        return ['action'];
    }

    public function getResolvedLabelValues(Request $request): array
    {
        if ($this->labelResolver) {
            return array_values($this->labelResolver->resolveLabels($request));
        }

        return [];
    }

    public function getAllLabelValues(): array
    {
        if ($this->labelResolver) {
            $resolveLabels = $this->labelResolver->getLabelNames();

            // Fill the "all" label with empty string, to match the number of labels.
            return array_merge(['all'], array_fill(0, count($resolveLabels), ''));
        }

        return ['all'];
    }
}
