<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * @deprecated Use ResponseMetricsCollectorInterface instead.
 */
interface TerminateMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectResponse(TerminateEvent $event): void;
}
