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
                ]
            ],
            [
                'redis (no password)',
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 1234,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ]
            ],
            [
                'redis unix-socket (no password)',
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '/var/run/redis/redis.sock',
                        'port' => null,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider configDataProvider
     * @doesNotPerformAssertions
     */
    public function testGetConfigTreeBuilder(string $description, array $config) {
        $cfg = new Configuration();
        $treeBuilder = $cfg->getConfigTreeBuilder();
        $tree = $treeBuilder->buildTree();
        $tree->finalize($config);
    }
}