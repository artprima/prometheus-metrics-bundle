<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RegisterMetricsGeneratorPass is a compilation pass that registers all metrics classes taged as
 * prometheus_metrics_bundle.metrics_generator.
 */
class RegisterMetricsGeneratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition(MetricsGeneratorRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(MetricsGeneratorRegistry::class);

        foreach ($container->findTaggedServiceIds('prometheus_metrics_bundle.metrics_generator') as $id => $tags) {
            $generator = $container->getDefinition($id);
            $generator->addMethodCall('init', [
                $container->getParameter('prometheus_metrics_bundle.namespace'),
                new Reference('prometheus_metrics_bundle.collector_registry'),
            ]);
            $definition->addMethodCall('registerMetricsGenerator', [new Reference($id)]);
        }
    }
}
