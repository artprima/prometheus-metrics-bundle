<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Class RequestCounterListener.
 */
class RequestCounterListener
{
    private const STOPWATCH_CLASS = '\Symfony\Component\Stopwatch\Stopwatch';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var AppMetrics
     */
    private $metrics;

    /**
     * @var array
     */
    private $ignoredRoutes;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(AppMetrics $metrics, ?LoggerInterface $logger = null, array $ignoredRoutes = ['metrics_prometheus'])
    {
        if (class_exists(self::STOPWATCH_CLASS)) {
            $className = self::STOPWATCH_CLASS;
            $this->stopwatch = new $className();
        }
        $this->metrics = $metrics;
        $this->logger = $logger ?? new NullLogger();
        $this->ignoredRoutes = $ignoredRoutes;
    }

    private function shouldRegister(string $requestMethod, ?string $requestRoute): bool
    {
        if ('OPTIONS' === $requestMethod) {
            return false;
        }

        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return false;
        }

        return true;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (null !== $this->stopwatch) {
            $this->stopwatch->start('execution_time');
        }

        $requestMethod = $event->getRequest()->getMethod();
        $requestRoute = $event->getRequest()->attributes->get('_route');

        if (!$this->shouldRegister($requestMethod, $requestRoute)) {
            return;
        }

        $this->metrics->setInstance($event->getRequest()->server->get('HOSTNAME') ?? 'dev');

        try {
            $this->metrics->incRequestsTotal($requestMethod, $requestRoute);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['from' => 'request_counter']);
        }
    }

    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $evt = $this->stopwatch ? $this->stopwatch->stop('execution_time') : null;

        $response = $event->getResponse();

        $requestMethod = $event->getRequest()->getMethod();
        $requestRoute = $event->getRequest()->attributes->get('_route');

        if (!$this->shouldRegister($requestMethod, $requestRoute)) {
            return;
        }

        try {
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $this->metrics->inc2xxResponsesTotal($requestMethod, $requestRoute);
            } elseif ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
                $this->metrics->inc4xxResponsesTotal($requestMethod, $requestRoute);
            } elseif ($response->getStatusCode() >= 500) {
                $this->metrics->inc5xxResponsesTotal($requestMethod, $requestRoute);
            }

            if (null !== $evt) {
                $this->metrics->setRequestDuration($evt->getDuration() / 1000, $requestMethod, $requestRoute);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['from' => 'request_counter']);
        }
    }
}
