<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * ExceptionMetricsCollectorInterface is an extention to the base MetricsCollectorInterface,
 * which allows you to collect metrics when an exception occurs in your code.
 */
interface ExceptionMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectException(ExceptionEvent $event): void;
}
