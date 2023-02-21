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
                'apcu',
                [
                    'namespace' => 'myapp',
                    'type' => 'apcu',
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'apcu',
                    'storage' => ['type' => 'apcu'],
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'disable_default_metrics' => false,
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                ],
            ],
            [
                'apcng',
                [
                    'namespace' => 'myapp',
                    'type' => 'apcng',
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'apcng',
                    'storage' => ['type' => 'apcng'],
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'disable_default_metrics' => false,
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                ],
            ],
            [
                'in_memory',
                [
                    'namespace' => 'myapp',
                    'type' => 'in_memory',
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'in_memory',
                    'storage' => ['type' => 'in_memory'],
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'disable_default_metrics' => false,
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
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
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => '10',
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                    'disable_default_metrics' => false,
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                    'storage' => ['type' => 'redis'],
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
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
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                ],
                [
                    'namespace' => 'myapp',
                    'type' => 'redis',
                    'redis' => [
                        'host' => '/var/run/redis/redis.sock',
                        'timeout' => 0.1,
                        'read_timeout' => '10',
                        'persistent_connections' => false,
                        'password' => null,
                        'port' => 6379,
                    ],
                    'disable_default_metrics' => false,
                    'disable_default_promphp_metrics' => false,
                    'enable_console_metrics' => false,
                    'storage' => ['type' => 'redis'],
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                ],
            ],
            [
                'redis storage dsn',
                [
                    'namespace' => 'myapp',
                    'storage' => 'redis://127.0.0.1:6379?timeout=0.1&read_timeout=10&persistent_connections=false',
                    'disable_default_metrics' => false,
                    'enable_console_metrics' => false,
                ],
                [
                    'namespace' => 'myapp',
                    'storage' => [
                        'url' => 'redis://127.0.0.1:6379?timeout=0.1&read_timeout=10&persistent_connections=false',
                    ],
                    'disable_default_metrics' => false,
                    'enable_console_metrics' => false,
                    'type' => 'in_memory',
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'disable_default_promphp_metrics' => false,
                ],
            ],
            [
                'redis storage',
                [
                    'namespace' => 'myapp',
                    'storage' => [
                        'type' => 'redis',
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                    'disable_default_metrics' => false,
                    'enable_console_metrics' => false,
                ],
                [
                    'namespace' => 'myapp',
                    'storage' => [
                        'type' => 'redis',
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0.1,
                        'read_timeout' => 10,
                        'persistent_connections' => false,
                        'password' => null,
                    ],
                    'disable_default_metrics' => false,
                    'enable_console_metrics' => false,
                    'type' => 'in_memory',
                    'ignored_routes' => ['prometheus_bundle_prometheus'],
                    'disable_default_promphp_metrics' => false,
                ],
            ],
        ];
    }

    public function invalidConfigDataProvider(): array
    {
        return [
            [
                'Invalid prefix',
                [
                    'type' => 'in_memory',
                    'namespace' => 'my_app',
                    'storage' => [
                        'prefix' => 'prefix-with-dash',
                    ],
                ],
                'Invalid configuration for path "artprima_prometheus_metrics.storage.prefix": Invalid prefix. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$',
            ],
        ];
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testGetConfigTreeBuilder(string $description, array $config, array $expected)
    {
        $cfg = new Configuration();
        $tree = $cfg->getConfigTreeBuilder()->buildTree();
        $result = $tree->finalize($tree->normalize($config));

        self::assertSame($expected, $result, $description);
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
        $tree->finalize($config);
    }
}
