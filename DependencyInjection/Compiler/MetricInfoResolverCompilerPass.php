<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MetricInfoResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
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
            $definition->addMethodCall('setMetricInfoResolver', [$id]);
        }
    }
}
