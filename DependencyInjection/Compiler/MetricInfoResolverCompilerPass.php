<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\MetricInfoResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass searches for services tagged with 'prometheus_metrics_bundle.metric_info_resolver'.
 * Thanks to this AppMetrics service can be configured with custom MetricInfoResolver which allows to customize the metric labels and recorded values.
 *
 * @see MetricInfoResolverInterface
 */
class MetricInfoResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(AppMetrics::class);
        $taggedServices = $container->findTaggedServiceIds('prometheus_metrics_bundle.metric_info_resolver');

        if ([] === $taggedServices) {
            return;
        }

        if (count($taggedServices) > 1) {
            throw new \RuntimeException('Only one metric info resolver can be defined');
        }

        foreach ($taggedServices as $id => $tags) {
            $definition->addArgument(new Reference($id));
        }
    }
}
