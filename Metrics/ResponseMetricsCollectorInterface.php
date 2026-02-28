<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * ResponseMetricsCollectorInterface lets collecting metrics on "kernel.response" event.
 */
interface ResponseMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectResponse(ResponseEvent $event): void;
}
