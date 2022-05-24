<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel;

/**
 * @group functional
 */
class BundleDisablePromMetricsTest extends WebTestCase
{
    public function testDisableDefaultPrometheusMetrics(): void
    {
        $client = self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'config_disable_default_prometheus_metrics.yml']);
        $client->request('GET', '/metrics/prometheus');
        self::assertStringNotContainsString('php_info{version=', $client->getResponse()->getContent());
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/../Fixtures/App/AppKernel.php';

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
