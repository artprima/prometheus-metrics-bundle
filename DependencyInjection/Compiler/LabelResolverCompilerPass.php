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
    /**
     * Setup setLabelResolver method call for AppMetrics service.
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(AppMetrics::class);
        $definition->addMethodCall('setLabelResolver', [new Reference(LabelResolver::class)]);
    }
}
