<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages the bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ArtprimaPrometheusMetricsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(MetricsCollectorInterface::class)
            ->addTag('prometheus_metrics_bundle.metrics_collector');

        $container->setParameter('prometheus_metrics_bundle.namespace', $config['namespace']);
        $container->setParameter('prometheus_metrics_bundle.type', $config['type']);
        if ('redis' === $config['type']) {
            $container->setParameter('prometheus_metrics_bundle.redis', $config['redis']);
        }
        $container->setParameter('prometheus_metrics_bundle.ignored_routes', $config['ignored_routes']);
        $container->setParameter('prometheus_metrics_bundle.disable_default_metrics', $config['disable_default_metrics']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
