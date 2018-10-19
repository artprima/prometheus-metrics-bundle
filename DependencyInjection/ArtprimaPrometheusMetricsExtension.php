<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Artprima\PrometheusMetricsBundle\EventListener\RequestCounterListener;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
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

        $adapterClasses = [
            'in_memory' => InMemory::class,
            'apcu' => APC::class,
            'redis' => Redis::class,
        ];

        $container->setParameter('prometheus_metrics_bundle.namespace', $config['namespace']);
        $container->setParameter('prometheus_metrics_bundle.type', $config['type']);
        if ('redis' === $config['type']) {
            $container->setParameter('prometheus_metrics_bundle.redis', $config['redis']);
        }
        $container->setParameter('prometheus_metrics_bundle.ignored_routes', $config['ignored_routes']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
