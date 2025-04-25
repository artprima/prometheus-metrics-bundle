<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle;

use Prometheus\Storage\Redis;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel;

/**
 * @group functional
 */
class BundleEnvRedisTest extends WebTestCase
{
    public function testBundle(): void
    {
        if (!\extension_loaded('redis')) {
            self::markTestSkipped('Cannot find the "redis" extension.');
        }

        $_ENV['TESTS_REDIS_URL'] = 'redis://127.0.0.1:6379?timeout=0.1&read_timeout=10&persistent_connections=false';

        $client = self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'env_config_redis.yml']);
        $client->request('GET', '/metrics/prometheus');

        self::assertStringContainsString('myapp_instance_name{instance="dev"} 1', $client->getResponse()->getContent());
        self::assertInstanceOf(Redis::class, self::getContainer()->get('prometheus_metrics_bundle.adapter'));
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/Fixtures/App/AppKernel.php';

        return AppKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            self::getVarDir(),
            $options['test_case'],
            $options['root_config'] ?? 'config.yml',
            $options['environment'] ?? strtolower(static::getVarDir().$options['test_case']),
            $options['debug'] ?? true
        );
    }

    private static function getVarDir(): string
    {
        return 'FB'.substr(strrchr(static::class, '\\'), 1);
    }
}
