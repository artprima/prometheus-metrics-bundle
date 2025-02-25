<?php

declare(strict_types=1);
namespace Tests\Artprima\PrometheusMetricsBundle;

use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use Artprima\PrometheusMetricsBundle\Tests\Fixtures\App\MetricInfoResolverAppKernel;
use Prometheus\CollectorRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @group functional
 */
class BundleMetricInfoResolverTest extends WebTestCase
{
    public function testPickingMetricInfoResolverFromContainer(): void
    {
        $client = self::createClient(['test_case' => 'PrometheusMetricsBundle', 'root_config' => 'config_in_memory.yml']);
        $client->request('get', '/dummy/test-action');
        self::assertThat($client->getResponse()->getStatusCode(), self::equalTo(200));

        /** @var CollectorRegistry $collectorRegistry */
        $collectorRegistry = self::getContainer()->get('prometheus_metrics_bundle.collector_registry');
        $renderer = new Renderer($collectorRegistry);
        $content = $renderer->renderResponse()->getContent();

        self::assertStringContainsString('myapp_http_2xx_responses_total{action="GET /dummy/test-action"} 1', $content);
        self::assertStringContainsString('myapp_http_requests_total{action="GET /dummy/test-action"} 1', $content);
        self::assertStringContainsString('myapp_http_2xx_responses_total{action="GET /dummy/test-action"} 1', $content);
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/Fixtures/App/MetricInfoResolverAppKernel.php';

        return MetricInfoResolverAppKernel::class;
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
