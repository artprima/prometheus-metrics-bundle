<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\BaseNode;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('artprima_prometheus_metrics');
        $rootNode = $treeBuilder->getRootNode();

        $supportedTypes = ['in_memory', 'apcu', 'apcng', 'redis'];

        $rootNode
            // Manage deprecated parameter "type": will be transform as storage.url
            ->beforeNormalization()
                ->ifTrue(static function ($v) {
                    return !empty($v['type']) && empty($v['storage']);
                })
                ->then(static function ($v) {
                    $v['storage'] = ['type' => $v['type']];

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('namespace')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('type')
                    ->setDeprecated(
                        ...$this->getDeprecationMsg('The type config parameter was deprecated in 1.14 and will be dropped in 2.0.', '1.14')
                    )
                    ->validate()
                        ->ifNotInArray($supportedTypes)
                        ->thenInvalid('The type %s is not supported. Please choose one of '.json_encode($supportedTypes))
                    ->end()
                    ->defaultValue('in_memory')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('redis')
                    ->setDeprecated(
                        ...$this->getDeprecationMsg('The redis config parameter was deprecated in 1.14 and will be dropped in 2.0.', '1.14')
                    )
                    ->children()
                        ->scalarNode('host')->end()
                        ->integerNode('port')
                            ->defaultValue(6379)
                        ->end()
                        ->floatNode('timeout')->end()
                        ->floatNode('read_timeout')
                            ->validate()
                                ->always()
                                // here we force casting `float` to `string` to avoid TypeError when working with Redis
                                // see for more details: https://github.com/phpredis/phpredis/issues/1538
                                ->then(function ($v) {
                                    return (string) $v;
                                })
                            ->end()
                        ->end()
                        ->booleanNode('persistent_connections')->end()
                        ->scalarNode('password')->end()
                        ->integerNode('database')->end()
                        ->scalarNode('prefix')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(function ($s) {
                                    return 1 !== preg_match('/^[a-zA-Z_:][a-zA-Z0-9_:]*$/', $s);
                                })
                                ->thenInvalid('Invalid prefix. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('storage')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            return ['url' => $v];
                        })
                    ->end()
                    ->validate()
                        ->always()
                        ->then(static function ($v) {
                            if (empty($v['options'])) {
                                unset($v['options']);
                            }

                            return $v;
                        })
                    ->end()
                    ->fixXmlConfig('option')
                    ->children()
                        ->scalarNode('url')->info('DSN of the storage. All parsed values will override explicitly set parameters. Ex: redis://127.0.0.1?timeout=0.1')->end()
                        ->scalarNode('type')->info('The type of storage provide by factories. Default factories are '.json_encode($supportedTypes))->end()
                        ->scalarNode('host')->info('Use by some factory like redis. Default value should be managed by the factory at runtime.')->end()
                        ->integerNode('port')->info('Use by some factory like redis. Default value should be managed by the factory at runtime.')->end()
                        ->floatNode('timeout')->info('Connection timeout used by some factory like redis.')->end()
                        ->floatNode('read_timeout')->end()
                        ->booleanNode('persistent_connections')->end()
                        ->scalarNode('password')->end()
                        ->integerNode('database')->end()
                        ->scalarNode('prefix')
                            ->info('Internal prefix used by the storage. Available for redis and apcu type.')
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(function ($s) {
                                    return 1 !== preg_match('/^[a-zA-Z_:][a-zA-Z0-9_:]*$/', $s);
                                })
                                ->thenInvalid('Invalid prefix. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$')
                            ->end()
                        ->end()
                        ->arrayNode('options')
                            ->info('Custom factory could use this parameter to add more parameters.')
                            ->useAttributeAsKey('key')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('ignored_routes')
                    ->prototype('scalar')->end()
                    ->defaultValue(['prometheus_bundle_prometheus'])
                ->end()
                ->booleanNode('disable_default_metrics')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('disable_default_promphp_metrics')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('enable_console_metrics')
                    ->defaultValue(false)
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    /**
     * Returns the correct deprecation param's as an array for setDeprecated.
     */
    private function getDeprecationMsg(string $message, string $version): array
    {
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return [
                'artprima/prometheus-metrics-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }
}
