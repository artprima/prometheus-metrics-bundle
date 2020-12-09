<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveAdapterDefinitionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('prometheus_metrics_bundle.adapter')) {
            return;
        }

        $adapterClasses = [
            'in_memory' => InMemory::class,
            'apcu' => APC::class,
            'redis' => Redis::class,
        ];

        $definition = $container->getDefinition('prometheus_metrics_bundle.adapter');
        $definition->setAbstract(false);
        $definition->setClass($adapterClasses[$container->getParameter('prometheus_metrics_bundle.type')]);
        if ('redis' === $container->getParameter('prometheus_metrics_bundle.type')) {
            $definition->setArguments([$container->getParameter('prometheus_metrics_bundle.redis')]);
        }
    }
}
