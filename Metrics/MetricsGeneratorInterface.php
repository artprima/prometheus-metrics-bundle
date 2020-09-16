<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

interface MetricsGeneratorInterface
{
    public function init(string $namespace, CollectorRegistry $collectionRegistry);

    public function collectStart(RequestEvent $event);

    public function collectRequest(RequestEvent $event);

    public function collectResponse(TerminateEvent $event);
}
