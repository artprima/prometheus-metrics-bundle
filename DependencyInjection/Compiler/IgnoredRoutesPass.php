<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\EventListener\RequestCounterListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IgnoredRoutesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition(RequestCounterListener::class)) {
            return;
        }

        $ignoredRoutes = $container->getParameter('prometheus_metrics_bundle.ignored_routes');
        $container->getDefinition(RequestCounterListener::class)->addArgument($ignoredRoutes);
    }
}
