<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\EventListener\MetricsCollectorListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * IgnoredRoutesPass is a compilation pass that sets ignored routes argument for the metrics.
 */
class IgnoredConsoleCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MetricsCollectorListener::class)) {
            return;
        }

        $ignoredConsoleCommands = $container->getParameter('prometheus_metrics_bundle.ignored_console_commands');
        $container->getDefinition(MetricsCollectorListener::class)->setArgument(
            '$ignoredConsoleCommands',
            $ignoredConsoleCommands
        );
    }
}
