<?php

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class AppMetricsTest extends TestCase
{
    private $namespace;
    private $collectionRegistry;
    /**
     * @var RendererTest
     */
    private $renderer;

    public function setUp()
    {
        $this->namespace = 'dummy';
        $this->collectionRegistry = new CollectorRegistry(new InMemory());
        $this->renderer = new Renderer($this->collectionRegistry);
    }

    public function testCollectRequest()
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);

        $metrics->collectRequest($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        $this->assertContains('dummy_instance_name{instance="dev"} 1', $responseContent);
        $this->assertContains("dummy_http_requests_total{action=\"all\"} 1\n", $responseContent);
        $this->assertContains("dummy_http_requests_total{action=\"GET-test_route\"} 1\n", $responseContent);
    }

    public function testCollectRequestOptionsMethod()
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'OPTIONS']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);

        $metrics->collectRequest($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        $this->assertEquals('', trim($responseContent));
    }

    public function provideMetricsName()
    {
        return [
            [200, 'http_2xx_responses_total'],
            [400, 'http_4xx_responses_total'],
            [500, 'http_5xx_responses_total'],
        ];
    }

    /**
     * @dataProvider provideMetricsName
     */
    public function testCollectResponse($code, $metricsName)
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(TerminateEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $response = new Response('', $code);
        $evt->expects(self::any())->method('getResponse')->willReturn($response);

        $metrics->collectResponse($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        $this->assertContains("dummy_{$metricsName}{action=\"all\"} 1\n", $responseContent);
        $this->assertContains("dummy_{$metricsName}{action=\"GET-test_route\"} 1\n", $responseContent);
    }

    public function testSetRequestDuration()
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $reqEvt = $this->createMock(RequestEvent::class);
        $reqEvt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt = $this->createMock(TerminateEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $response = new Response('', 200);
        $evt->expects(self::any())->method('getResponse')->willReturn($response);

        $metrics->collectStart($reqEvt);
        $metrics->collectRequest($reqEvt);
        $metrics->collectResponse($evt);
        $response = $this->renderer->renderResponse();
        $content = $response->getContent();
        $this->assertContains('dummy_request_durations_histogram_seconds_bucket{action="GET-test_route",le=', $content);
        $this->assertContains('dummy_request_durations_histogram_seconds_count{action="GET-test_route"}', $content);
        $this->assertContains('dummy_request_durations_histogram_seconds_sum{action="GET-test_route"}', $content);
        $this->assertContains('dummy_request_durations_histogram_seconds_bucket{action="all",le=', $content);
        $this->assertContains('dummy_request_durations_histogram_seconds_count{action="all"}', $content);
        $this->assertContains('dummy_request_durations_histogram_seconds_sum{action="all"}', $content);
    }
}
