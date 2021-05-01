<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * ConsoleTerminateMetricsCollectorInterface lets collecting metrics on "console.terminate" event.
 */
interface ConsoleTerminateMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectConsoleTerminate(ConsoleTerminateEvent $event): void;
}
