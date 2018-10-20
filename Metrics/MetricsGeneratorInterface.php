<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Prometheus\CollectorRegistry;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

interface MetricsGeneratorInterface
{
    public function init(string $namespace, CollectorRegistry $collectionRegistry);

    public function collectRequest(GetResponseEvent $event);

    public function collectResponse(PostResponseEvent $event);
}
