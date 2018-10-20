<?php

namespace Artprima\PrometheusMetricsBundle\Tests\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\InMemory;

class AppMetricsTest extends TestCase
{
    private $namespace;
    private $collectionRegistry;

    public function setUp()
    {
        $this->namespace = 'dummy';
        $this->collectionRegistry = new CollectorRegistry(new InMemory());
    }

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
        $metrics = new AppMetrics($this->namespace, $collectionRegistry);
        $response = $metrics->render();
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
        $metrics = new AppMetrics($this->namespace, $collectionRegistry);
        $response = $metrics->renderResponse();
        $this->assertContains("# HELP name help\n# TYPE name type\n", $response->getContent());
    }

    public function testSetInstance()
    {
        $metrics = new AppMetrics($this->namespace, $this->collectionRegistry);
        $metrics->setInstance('test_app');
        $response = $metrics->renderResponse();
        $this->assertContains('dummy_instance_name{instance="test_app"} 1', $response->getContent());
    }

    public function provideMetricsName()
    {
        return [
            ['incRequestsTotal', 'http_requests_total'],
            ['inc2xxResponsesTotal', 'http_2xx_responses_total'],
            ['inc4xxResponsesTotal', 'http_4xx_responses_total'],
            ['inc5xxResponsesTotal', 'http_5xx_responses_total'],
        ];
    }

    /**
     * @dataProvider provideMetricsName
     */
    public function testCounterMetrics($funcName, $metricName)
    {
        $metrics = new AppMetrics($this->namespace, $this->collectionRegistry);
        $metrics->$funcName();
        $response = $metrics->renderResponse();
        $this->assertContains("dummy_{$metricName}{action=\"all\"} 1\n", $response->getContent());

        $metrics->$funcName('POST', 'route_name');
        $response = $metrics->renderResponse();
        $this->assertContains("dummy_{$metricName}{action=\"POST-route_name\"} 1\n", $response->getContent());
    }

    public function testSetRequestDuration()
    {
        $metrics = new AppMetrics($this->namespace, $this->collectionRegistry);
        $metrics->setRequestDuration(0.5);
        $response = $metrics->renderResponse();
        $expected = <<<'EOD'
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.005"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.01"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.025"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.05"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.075"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.1"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.25"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.75"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="1"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="2.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="5"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="7.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="10"} 1
dummy_request_durations_histogram_seconds_bucket{action="all",le="+Inf"} 1
dummy_request_durations_histogram_seconds_count{action="all"} 1
dummy_request_durations_histogram_seconds_sum{action="all"} 0.5
EOD;

        $this->assertContains($expected, $response->getContent());

        $expected = <<<'EOD'
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.005"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.01"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.025"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.05"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.075"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.1"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.25"} 0
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="0.75"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="1"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="2.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="5"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="7.5"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="10"} 1
dummy_request_durations_histogram_seconds_bucket{action="POST-route_name",le="+Inf"} 1
dummy_request_durations_histogram_seconds_count{action="POST-route_name"} 1
dummy_request_durations_histogram_seconds_sum{action="POST-route_name"} 0.5
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.005"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.01"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.025"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.05"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.075"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.1"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.25"} 0
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.5"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="0.75"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="1"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="2.5"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="5"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="7.5"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="10"} 2
dummy_request_durations_histogram_seconds_bucket{action="all",le="+Inf"} 2
dummy_request_durations_histogram_seconds_count{action="all"} 2
dummy_request_durations_histogram_seconds_sum{action="all"} 1
EOD;

        $metrics->setRequestDuration(0.5, 'POST', 'route_name');
        $response = $metrics->renderResponse();
        $this->assertContains($expected, $response->getContent());
    }
}
