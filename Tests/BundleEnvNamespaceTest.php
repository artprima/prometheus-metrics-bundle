<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel;

/**
 * @group functional
 */
class BundleEnvNamespaceTest extends WebTestCase
{
    protected function tearDown(): void
    {
        $cacheDir = null !== static::$kernel ? static::$kernel->getCacheDir() : null;

        parent::tearDown();

        if (null !== $cacheDir) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }
    }

    public function testBundle(): void
    {
        $_ENV['TESTS_PROM_NAMESPACE'] = 'myapp_test';
        $_ENV['TESTS_STORAGE_URL'] = 'in_memory';

        $client = self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'env_config_namespace.yml']);
        $client->request('GET', '/metrics/prometheus');

        self::assertStringContainsString('myapp_test_instance_name{instance="dev"} 1', $client->getResponse()->getContent());
    }

    public function testInvalidNamespace(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException(
            'Invalid namespace. Make sure it matches the following regex: ^[a-zA-Z_:][a-zA-Z0-9_:]*$'
        ));

        $_ENV['TESTS_PROM_NAMESPACE'] = 'myapp-with-dash';
        $_ENV['TESTS_STORAGE_URL'] = 'in_memory';

        self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'env_config_namespace.yml']);
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

    private static function clearCache(): string
    {
        return 'FB'.substr(strrchr(static::class, '\\'), 1);
    }
}
