<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * ConsoleCommandMetricsCollectorInterface lets collecting metrics on "console.command" event.
 */
interface ConsoleCommandMetricsCollectorInterface extends MetricsCollectorInterface
{
    public function collectConsoleCommand(ConsoleCommandEvent $event): void;
}
