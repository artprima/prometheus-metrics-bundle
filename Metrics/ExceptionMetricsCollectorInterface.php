<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * ExceptionMetricsCollectorInterface lets collecting metrics on "kernel.exception" event.
 */
interface ExceptionMetricsCollectorInterface
{
    public function collectException(ExceptionEvent $event): void;
}
