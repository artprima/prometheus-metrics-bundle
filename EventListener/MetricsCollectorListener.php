<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\ConsoleCommandMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ConsoleErrorMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ConsoleTerminateMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use Artprima\PrometheusMetricsBundle\Metrics\PreExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\PreRequestMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\RequestMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\TerminateMetricsCollectorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class MetricsCollectorListener is an event listener that calls the registered metric handlers.
 */
class MetricsCollectorListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private MetricsCollectorRegistry $metricsCollectors;

    private array $ignoredRoutes;

    public function __construct(MetricsCollectorRegistry $metricsCollectors, array $ignoredRoutes = ['prometheus_bundle_prometheus'])
    {
        $this->metricsCollectors = $metricsCollectors;
        $this->ignoredRoutes = $ignoredRoutes;
    }

    public function onKernelRequestPre(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectStart', PreRequestMetricsCollectorInterface::class)) {
                continue;
            }

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
        if (!$event->isMainRequest()) {
            return;
        }

        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectRequest', RequestMetricsCollectorInterface::class)) {
                continue;
            }

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

    public function onKernelExceptionPre(ExceptionEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectPreException', PreExceptionMetricsCollectorInterface::class)) {
                continue;
            }

            try {
                $collector->collectPreException($event);
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

    public function onKernelException(ExceptionEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectException', ExceptionMetricsCollectorInterface::class)) {
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
            if (!self::isSupportedEvent($collector, 'collectResponse', TerminateMetricsCollectorInterface::class)) {
                continue;
            }

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

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectConsoleCommand', ConsoleCommandMetricsCollectorInterface::class)) {
                continue;
            }

            try {
                $collector->collectConsoleCommand($event);
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

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectConsoleTerminate', ConsoleTerminateMetricsCollectorInterface::class)) {
                continue;
            }

            try {
                $collector->collectConsoleTerminate($event);
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

    public function onConsoleError(ConsoleErrorEvent $event)
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!self::isSupportedEvent($collector, 'collectConsoleError', ConsoleErrorMetricsCollectorInterface::class)) {
                continue;
            }

            try {
                $collector->collectConsoleError($event);
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

    private static function isSupportedEvent(MetricsCollectorInterface $collector, string $method, string $interface): bool
    {
        if (is_subclass_of($collector, $interface)) {
            // supported
            return true;
        }

        if (!is_callable([$collector, $method])) {
            // not supported
            return false;
        }

        @trigger_error(sprintf(
            'Metrics Collector has a public method %s but doesn\'t implement %s.',
            $method,
            $interface
        ), E_USER_DEPRECATED);

        // supported, but deprecated
        return true;
    }
}
