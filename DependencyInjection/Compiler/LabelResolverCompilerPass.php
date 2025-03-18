<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LabelResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(AppMetrics::class);

        if ($container->hasParameter('prometheus_metrics_bundle.labels')) {
            $labelConfig = $container->getParameter('prometheus_metrics_bundle.labels');
            if (!empty($labelConfig)) {
                $definition->addMethodCall('setLabelResolver', [new Reference(LabelResolver::class)]);
            }
        }
    }
}
