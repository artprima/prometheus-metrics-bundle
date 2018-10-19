<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('artprima_prometheus_metrics');

        $supportedTypes = ['in_memory', 'apcu', 'redis'];

        $rootNode
            ->children()
                ->scalarNode('namespace')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('type')
                    ->validate()
                        ->ifNotInArray($supportedTypes)
                        ->thenInvalid('The type %s is not supported. Please choose one of '.json_encode($supportedTypes))
                    ->end()
                    ->defaultValue('in_memory')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('redis')
                    ->children()
                        ->scalarNode('host')->end()
                        ->integerNode('port')->end()
                        ->floatNode('timeout')->end()
                        ->floatNode('read_timeout')->end()
                        ->booleanNode('persistent_connections')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
                ->arrayNode('ignored_routes')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
