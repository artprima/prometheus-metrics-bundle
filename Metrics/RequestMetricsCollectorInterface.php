<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * RequestMetricsCollectorInterface lets collecting metrics on "kernel.request" event.
 */
interface RequestMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectRequest(RequestEvent $event): void;
}
