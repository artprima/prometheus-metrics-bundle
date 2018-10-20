<?php

namespace Artprima\PrometheusMetricsBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;

/**
 * @group functional
 */
class BundleTest extends WebTestCase
{
    protected static function getKernelClass()
    {
        require_once __DIR__.'/Fixtures/App/AppKernel.php';

        return 'Tests\Artprima\PrometheusMetricsBundle\Fixtures\App\AppKernel';
    }

    protected static function createKernel(array $options = array())
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            static::getVarDir(),
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : strtolower(static::getVarDir().$options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    public function testBundle()
    {
        $client = $this->createClient(array('test_case' => 'PrometheusMetricsBundle', 'root_config' => 'config.yml'));
        $client->request('GET', '/metrics/prometheus');
        $this->assertContains('myapp_instance_name{instance="dev"} 1', $client->getResponse()->getContent());
    }
}
