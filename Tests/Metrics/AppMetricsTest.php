<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Metrics;

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\LabelConfig;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
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
        $this->labelResolver = new LabelResolver([]);
    }

    public function testCollectRequest(): void
    {
        $metrics = new AppMetrics($this->labelResolver);
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
        $metrics = new AppMetrics($this->labelResolver);
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
            trim((string) $responseContent)
        );
    }

    public static function provideMetricsName(): array
    {
        return [
            [200, 'http_2xx_responses_total'],
            [300, 'http_3xx_responses_total'],
            [400, 'http_4xx_responses_total'],
            [500, 'http_5xx_responses_total'],
        ];
    }

    /**
     * @dataProvider provideMetricsName
     */
    public function testCollectResponse(int $code, string $metricsName): void
    {
        $metrics = new AppMetrics($this->labelResolver);
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = new Response('', $code);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $evt = new TerminateEvent($kernel, $request, $response);
        $metrics->collectResponse($evt);

        $response = $this->renderer->renderResponse();
        $responseContent = $response->getContent();

        self::assertStringContainsString("dummy_$metricsName{action=\"all\"} 1\n", $responseContent);
        self::assertStringContainsString("dummy_$metricsName{action=\"GET-test_route\"} 1\n", $responseContent);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetRequestDuration(): void
    {
        self::registerMicrotimeMock('Artprima\PrometheusMetricsBundle\Metrics');

        $metrics = new AppMetrics($this->labelResolver);
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
        self::assertStringContainsString(
            <<<'PHPEOL'
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
            PHPEOL,
            $content
        );
    }

    public function testUseMetricInfoResolver(): void
    {
        $metrics = new AppMetrics($this->labelResolver, new DummyMetricInfoResolver());
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => 'https://example.com/test?query=1']);
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

        self::assertStringContainsString('dummy_http_2xx_responses_total{action="GET /test"} 1', $content);
        self::assertStringContainsString('dummy_http_requests_total{action="GET /test"} 1', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_bucket{action="GET /test",le="0.005"} 1', $content);
        self::assertStringContainsString('dummy_request_durations_histogram_seconds_count{action="GET /test"} 1', $content);
    }

    public function testUseMetricInfoResolverWithLabels(): void
    {
        $labels = [
            new LabelConfig('color', LabelConfig::REQUEST_ATTRIBUTE, 'color'),
            new LabelConfig('client_name', LabelConfig::REQUEST_HEADER, 'X-Client-Name'),
        ];

        $metrics = new AppMetrics(new LabelResolver($labels), new DummyMetricInfoResolver());
        $metrics->init($this->namespace, $this->collectionRegistry);

        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => 'https://example.com/test?query=1']);

        // Setting color attribute
        $request->attributes->set('color', 'red');

        // Setting X-Client-Name header
        $request->headers->set('X-Client-Name', 'mobile-app');

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

        static::assertStringContainsString('dummy_http_2xx_responses_total{action="GET /test",color="red",client_name="mobile-app"} 1', $content);
        static::assertStringContainsString('dummy_http_requests_total{action="GET /test",color="red",client_name="mobile-app"} 1', $content);
        static::assertStringContainsString('dummy_request_durations_histogram_seconds_bucket{action="GET /test",color="red",client_name="mobile-app",le="0.005"} 1', $content);
        static::assertStringContainsString('dummy_request_durations_histogram_seconds_count{action="GET /test",color="red",client_name="mobile-app"} 1', $content);
    }

    public static function microtime($asFloat = false)
    {
        static $sequence = -1;
        ++$sequence;
        if (0 === $sequence % 2) {
            return 0; // start time
        }

        return 0.5; // stop time
    }

    private static function registerMicrotimeMock($ns)
    {
        $self = static::class;

        if (\function_exists($ns.'\microtime')) {
            return;
        }
        eval(<<<EOPHP
namespace $ns;

function microtime(\$asFloat = false)
{
    return \\$self::microtime(\$asFloat);
}
EOPHP);
    }
}
