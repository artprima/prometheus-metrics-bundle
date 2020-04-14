<?php

namespace Artprima\PrometheusMetricsBundle\Tests\DependencyInjection;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function configDataProvider(): array {
        return [
            [
                'in_memory',
                [
                    'namespace' => 'myapp',
                    'type' => 'in_memory',
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'in_memory',
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                ],
            ],
            [
                'redis (no password)',
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ],
            ],
            [
                'redis unix-socket (no password)',
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '/var/run/redis/redis.sock',
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'redis' => [
                        'host' => '/var/run/redis/redis.sock',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => '10',
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider configDataProvider
     * @ doesNotPerformAssertions
     */
    public function testGetConfigTreeBuilder(string $description, array $config, array $expected) {
        $cfg = new Configuration();
        $treeBuilder = $cfg->getConfigTreeBuilder();
        $tree = $treeBuilder->buildTree();
        $result = $tree->finalize($config);
        $this->assertEquals($expected, $result, $description);
    }
}