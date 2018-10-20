<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;

class RendererTest extends TestCase
{
    public function testRender()
    {
        $collectionRegistry = $this->createMock(CollectorRegistry::class);
        $collectionRegistry->expects(self::once())->method('getMetricFamilySamples')->willReturn([new MetricFamilySamples([
            'name' => 'name',
            'type' => 'type',
            'help' => 'help',
            'labelNames' => [],
            'samples' => [],
        ])]);
        $metrics = new AppMetrics();
        $metrics->init('test_ns', $collectionRegistry);
        $renderer = new Renderer($collectionRegistry);
        $response = $renderer->render();
        $this->assertEquals("# HELP name help\n# TYPE name type\n", $response);
    }

    public function testRenderResponse()
    {
        $collectionRegistry = $this->createMock(CollectorRegistry::class);
        $collectionRegistry->expects(self::once())->method('getMetricFamilySamples')->willReturn([new MetricFamilySamples([
            'name' => 'name',
            'type' => 'type',
            'help' => 'help',
            'labelNames' => [],
            'samples' => [],
        ])]);
        $metrics = new AppMetrics();
        $metrics->init('test_ns', $collectionRegistry);
        $renderer = new Renderer($collectionRegistry);
        $response = $renderer->renderResponse();
        $this->assertContains("# HELP name help\n# TYPE name type\n", $response->getContent());
    }
}
