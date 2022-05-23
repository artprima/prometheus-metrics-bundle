<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
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
        $container->setParameter('prometheus_metrics_bundle.enable_default_prometheus_metrics', !$config['disable_default_prometheus_metrics']);
        $container->setParameter('prometheus_metrics_bundle.enable_console_metrics', $config['enable_console_metrics']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
