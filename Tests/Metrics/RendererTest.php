<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;

class RendererTest extends TestCase
{
    private static $samples = [
        'name' => 'name',
        'type' => 'type',
        'help' => 'help',
        'labelNames' => [],
        'samples' => [],
    ];

    public function testRender(): void
    {
        $collectionRegistry = $this->createMock(CollectorRegistry::class);
        $collectionRegistry
            ->expects(self::once())
            ->method('getMetricFamilySamples')
            ->willReturn([
                new MetricFamilySamples(self::$samples),
            ]);
        $metrics = new AppMetrics(new LabelResolver());
        $metrics->init('test_ns', $collectionRegistry);
        $renderer = new Renderer($collectionRegistry);
        $response = $renderer->render();
        self::assertEquals("# HELP name help\n# TYPE name type\n", $response);
    }

    public function testRenderResponse(): void
    {
        $collectionRegistry = $this->createMock(CollectorRegistry::class);
        $collectionRegistry
            ->expects(self::once())
            ->method('getMetricFamilySamples')
            ->willReturn([
                new MetricFamilySamples(self::$samples),
            ]);
        $metrics = new AppMetrics(new LabelResolver());
        $metrics->init('test_ns', $collectionRegistry);
        $renderer = new Renderer($collectionRegistry);
        $response = $renderer->renderResponse();
        self::assertStringContainsString("# HELP name help\n# TYPE name type\n", $response->getContent());
    }
}
