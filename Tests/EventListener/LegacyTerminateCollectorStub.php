<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\TerminateMetricsCollectorInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class LegacyTerminateCollectorStub implements TerminateMetricsCollectorInterface
{
    public function collectResponse(TerminateEvent $event): void
    {
    }

    public function init(string $namespace, \Prometheus\CollectorRegistry $collectionRegistry): void
    {
    }
}
