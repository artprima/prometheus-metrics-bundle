<?php

declare(strict_types=1);

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
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AppMetricsTest extends TestCase
{
    private $namespace;
    private $collectionRegistry;
    /**
     * @var RendererTest
     */
    private $renderer;

    public function setUp(): void
    {
        $this->namespace = 'dummy';
        $this->collectionRegistry = new CollectorRegistry(new InMemory());
        $this->renderer = new Renderer($this->collectionRegistry);
    }

    public function testCollectRequest(): void
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);

        $metrics->collectRequest($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        self::assertStringContainsString('dummy_instance_name{instance="dev"} 1', $responseContent);
        self::assertStringContainsString("dummy_http_requests_total{action=\"all\"} 1\n", $responseContent);
        self::assertStringContainsString("dummy_http_requests_total{action=\"GET-test_route\"} 1\n", $responseContent);
    }

    public function testCollectRequestOptionsMethod(): void
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'OPTIONS']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);

        $metrics->collectRequest($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        $expected = "# HELP php_info Information about the PHP environment.\n# TYPE php_info gauge\nphp_info{version=\"%s\"} 1";

        self::assertStringContainsString(
            sprintf($expected, PHP_VERSION),
            trim($responseContent)
        );
    }

    public function provideMetricsName(): array
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
    public function testCollectResponse(int $code, string $metricsName): void
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = new Response('', $code);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $evt = new TerminateEvent($kernel, $request, $response);
        $metrics->collectResponse($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        self::assertStringContainsString("dummy_{$metricsName}{action=\"all\"} 1\n", $responseContent);
        self::assertStringContainsString("dummy_{$metricsName}{action=\"GET-test_route\"} 1\n", $responseContent);
    }

    public function testSetRequestDuration(): void
    {
        $metrics = new AppMetrics();
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $reqEvt = $this->createMock(RequestEvent::class);
        $reqEvt->method('getRequest')->willReturn($request);

        $response = new Response('', 200);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $evt = new TerminateEvent($kernel, $request, $response);

        $metrics->collectStart($reqEvt);
        $metrics->collectRequest($reqEvt);
        $metrics->collectResponse($evt);
        $response = $this->renderer->renderResponse();
        $content = $response->getContent();
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_bucket{action="GET-test_route",le=', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_count{action="GET-test_route"}', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_sum{action="GET-test_route"}', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_bucket{action="all",le=', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_count{action="all"}', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_sum{action="all"}', $content);
    }
}
