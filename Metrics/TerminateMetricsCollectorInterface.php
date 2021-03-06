<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * TerminateMetricsCollectorInterface lets collecting metrics on "kernel.terminate" event.
 */
interface TerminateMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectResponse(TerminateEvent $event): void;
}
