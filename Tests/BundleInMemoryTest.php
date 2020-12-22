<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel;

/**
 * @group functional
 */
class BundleInMemoryTest extends WebTestCase
{
    public function testBundle(): void
    {
        $client = self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'config_in_memory.yml']);
        $client->request('GET', '/metrics/prometheus');
        self::assertStringContainsString('myapp_instance_name{instance="dev"} 1', $client->getResponse()->getContent());
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/Fixtures/App/AppKernel.php';

        return AppKernel::class;
    }

    protected static function createKernel(array $options = [])
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
