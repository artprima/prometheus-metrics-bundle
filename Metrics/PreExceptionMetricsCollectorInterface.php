<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * PreExceptionMetricsCollectorInterface lets collecting metrics on "kernel.exception" event with a priority of 1024.
 */
interface PreExceptionMetricsCollectorInterface
{
    public function collectPreException(ExceptionEvent $event): void;
}
