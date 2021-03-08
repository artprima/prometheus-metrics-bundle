<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests\DependencyInjection;

use Artprima\PrometheusMetricsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigurationTest extends TestCase
{
    public function configDataProvider(): array
    {
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
                    'disable_default_metrics' => false,
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
                    'disable_default_metrics' => false,
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
                    'disable_default_metrics' => false,
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
                    'disable_default_metrics' => false,
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
                    'disable_default_metrics' => false,
                ],
            ],
        ];
    }

    public function invalidConfigDataProvider(): array
    {
        return [
            [
                'invalid namespace (with dashes)',
                [
                    'namespace' => 'myapp-with-dash',
                    'type' => 'in_memory',
                ],
                'Invalid configuration for path "artprima_prometheus_metrics.namespace": Invalid namespace. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$',
            ],
        ];
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testGetConfigTreeBuilder(string $description, array $config, array $expected)
    {
        $cfg = new Configuration();
        $treeBuilder = $cfg->getConfigTreeBuilder();
        $tree = $treeBuilder->buildTree();
        $result = $tree->finalize($config);
        self::assertEquals($expected, $result, $description);
    }

    /**
     * @dataProvider invalidConfigDataProvider
     */
    public function testGetConfigTreeBuilderInvalidConfig(string $description, array $config, string $exceptionMessage, int $exceptionCode = 0)
    {
        self::expectExceptionObject(new InvalidConfigurationException(
            $exceptionMessage,
            $exceptionCode
        ));

        $cfg = new Configuration();
        $treeBuilder = $cfg->getConfigTreeBuilder();
        $tree = $treeBuilder->buildTree();
        $result = $tree->finalize($config);
    }
}
