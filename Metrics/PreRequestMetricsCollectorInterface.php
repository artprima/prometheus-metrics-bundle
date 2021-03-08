<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * PreRequestMetricsCollectorInterface lets collecting metrics on "kernel.request" event with a priority of 1024.
 */
interface PreRequestMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectStart(RequestEvent $event): void;
}
