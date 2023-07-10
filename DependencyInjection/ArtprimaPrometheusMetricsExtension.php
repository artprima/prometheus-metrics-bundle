<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Compiler\ResolveAdapterDefinitionPass;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\StorageFactory\StorageFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages the bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ArtprimaPrometheusMetricsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(MetricsCollectorInterface::class)
            ->addTag('prometheus_metrics_bundle.metrics_collector');

        $container->registerForAutoconfiguration(StorageFactoryInterface::class)
            ->addTag(ResolveAdapterDefinitionPass::TAG_NAME);

        // see: https://github.com/artprima/prometheus-metrics-bundle/issues/80
        $namespace = $container->resolveEnvPlaceholders($config['namespace'], true);

        // see: https://github.com/artprima/prometheus-metrics-bundle/issues/32
        if (1 !== preg_match('/^[a-zA-Z_:][a-zA-Z0-9_:]*$/', $namespace)) {
            throw new \InvalidArgumentException('Invalid namespace. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$');
        }

        $container->setParameter('prometheus_metrics_bundle.namespace', $namespace);
        $container->setParameter('prometheus_metrics_bundle.storage', $config['storage']);

        if (isset($config['type'])) {
            $container->setParameter('prometheus_metrics_bundle.type', $config['type']);
            if ('redis' === $config['type']) {
                $container->setParameter('prometheus_metrics_bundle.redis', $config['redis']);
            }
        }

        $container->setParameter('prometheus_metrics_bundle.ignored_routes', $config['ignored_routes']);
        $container->setParameter('prometheus_metrics_bundle.disable_default_metrics', $config['disable_default_metrics']);
        $container->setParameter('prometheus_metrics_bundle.enable_default_promphp_metrics', !$config['disable_default_promphp_metrics']);
        $container->setParameter('prometheus_metrics_bundle.enable_console_metrics', $config['enable_console_metrics']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->prepareAdapterParameters($config, $container);
    }

    /**
     * Set the parameters for the factory of adapter.
     */
    private function prepareAdapterParameters(array $config, ContainerBuilder $container): void
    {
        $factoryParameters = $config['storage'];

        // Backward compatibility: add legacy redis configuration if set to the factory
        if (!empty($config['redis'])) {
            $factoryParameters = array_merge($factoryParameters, $config['redis']);
        }

        $container->getDefinition('prometheus_metrics_bundle.adapter')
            ->setArguments([$factoryParameters]);
    }
}
