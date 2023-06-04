<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler;

use Artprima\PrometheusMetricsBundle\StorageFactory\FactoryRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * ResolveAdapterDefinitionPass is a compilation pass that registers adapter factories.
 */
class ResolveAdapterDefinitionPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const TAG_NAME = 'prometheus_metrics_bundle.adapter_factory';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(FactoryRegistry::class)) {
            return;
        }

        if (!$factories = $this->findAndSortTaggedServices(self::TAG_NAME, $container)) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', self::TAG_NAME, FactoryRegistry::class));
        }

        $factoryRegistry = $container->getDefinition(FactoryRegistry::class);
        $factoryRegistry->replaceArgument(0, $factories);
    }
}
