<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\ExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class MetricsCollectorListener is an event listener that calls the registered metric handlers.
 */
class MetricsCollectorListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MetricsCollectorRegistry
     */
    private $metricsCollectors;

    /**
     * @var array
     */
    private $ignoredRoutes;

    public function __construct(MetricsCollectorRegistry $metricsCollectors, array $ignoredRoutes = ['prometheus_bundle_prometheus'])
    {
        $this->metricsCollectors = $metricsCollectors;
        $this->ignoredRoutes = $ignoredRoutes;
    }

    public function onKernelRequestPre(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            try {
                $collector->collectStart($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'request_collector', 'class' => get_class($collector)]
                    );
                }
            }
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            try {
                $collector->collectRequest($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'request_collector', 'class' => get_class($collector)]
                    );
                }
            }
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ExceptionMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectException($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => get_class($collector)]
                    );
                }
            }
        }
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            try {
                $collector->collectResponse($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => get_class($collector)]
                    );
                }
            }
        }
    }
}
