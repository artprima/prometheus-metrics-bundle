<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * MetricsCollectorInterface is a basic interface to be implemented by any metrics collector.
 */
interface MetricsCollectorInterface
{
    public function init(string $namespace, CollectorRegistry $collectionRegistry): void;

    public function collectStart(RequestEvent $event): void;

    public function collectRequest(RequestEvent $event): void;

    public function collectResponse(TerminateEvent $event): void;
}