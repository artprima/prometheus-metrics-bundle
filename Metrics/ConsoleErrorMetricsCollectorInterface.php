<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\Console\Event\ConsoleErrorEvent;

/**
 * ConsoleErrorMetricsCollectorInterface lets collecting metrics on "console.error" event.
 */
interface ConsoleErrorMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectConsoleError(ConsoleErrorEvent $event): void;
}
