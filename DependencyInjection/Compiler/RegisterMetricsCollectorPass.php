<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RegisterMetricsCollectorPass is a compilation pass that registers all metrics classes taged as
 * prometheus_metrics_bundle.metrics_collector. In addition it still supports old tags
 * prometheus_metrics_bundle.metrics_generator.
 */
class RegisterMetricsCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition(MetricsCollectorRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(MetricsCollectorRegistry::class);

        foreach ($container->findTaggedServiceIds('prometheus_metrics_bundle.metrics_generator') as $id => $tags) {
            @trigger_error(sprintf(
                'Service id "%s" uses tag "%s", which is deprecated, use "%s" instead.',
                $id,
                'prometheus_metrics_bundle.metrics_generator',
                'prometheus_metrics_bundle.metrics_collector'
            ), E_USER_DEPRECATED);
            $collector = $container->getDefinition($id);
            $collector->addMethodCall('init', [
                $container->getParameter('prometheus_metrics_bundle.namespace'),
                new Reference('prometheus_metrics_bundle.collector_registry'),
            ]);
            $definition->addMethodCall('registerMetricsCollector', [new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('prometheus_metrics_bundle.metrics_collector') as $id => $tags) {
            $collector = $container->getDefinition($id);
            $collector->addMethodCall('init', [
                $container->getParameter('prometheus_metrics_bundle.namespace'),
                new Reference('prometheus_metrics_bundle.collector_registry'),
            ]);
            $definition->addMethodCall('registerMetricsCollector', [new Reference($id)]);
        }
    }
}
