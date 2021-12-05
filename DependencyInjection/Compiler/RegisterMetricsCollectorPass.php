<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\EventListener\MetricsCollectorListener;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RegisterMetricsCollectorPass is a compilation pass that registers all metrics classes taged as
 * prometheus_metrics_bundle.metrics_collector. In addition, it still supports old tags
 * prometheus_metrics_bundle.metrics_generator.
 */
class RegisterMetricsCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition(MetricsCollectorRegistry::class)) {
            return;
        }

        $disableDefaultMetrics = $container->getParameter('prometheus_metrics_bundle.disable_default_metrics');

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

            if ($disableDefaultMetrics && $collector->hasTag('prometheus_metrics_bundle.default_metrics')) {
                // don't register default metrics, if it's disabled in the configuration.
                continue;
            }

            $collector->addMethodCall('init', [
                $container->getParameter('prometheus_metrics_bundle.namespace'),
                new Reference('prometheus_metrics_bundle.collector_registry'),
            ]);
            $definition->addMethodCall('registerMetricsCollector', [new Reference($id)]);
        }

        $consoleMetricsEnabled = $container->getParameter('prometheus_metrics_bundle.enable_console_metrics');
        if ($consoleMetricsEnabled) {
            $listenerDefinition = $container->getDefinition(MetricsCollectorListener::class);
            $listenerDefinition->addTag('kernel.event_listener', [
                'event' => 'console.command',
                'method' => 'onConsoleCommand',
            ]);
            $listenerDefinition->addTag('kernel.event_listener', [
                'event' => 'console.terminate',
                'method' => 'onConsoleTerminate',
            ]);
            $listenerDefinition->addTag('kernel.event_listener', [
                'event' => 'console.error',
                'method' => 'onConsoleError',
            ]);
        }
    }
}
