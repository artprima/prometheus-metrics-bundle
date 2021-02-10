<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\EventListener\RequestCounterListener;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestCounterListenerTest extends TestCase
{
    public function testOnKernelRequest(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);
        $evt->method('isMasterRequest')->willReturn(true);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectRequest')->with($evt);
        $collector2 = $this->createMock(MetricsCollectorInterface::class);
        $collector2->expects(self::once())->method('collectRequest')->with($evt);

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);
        $registry->registerMetricsCollector($collector2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestIgnoredRoute(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);
        $evt->method('isMasterRequest')->willReturn(true);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::never())->method('collectRequest');
        $collector2 = $this->createMock(MetricsCollectorInterface::class);
        $collector2->expects(self::never())->method('collectRequest');

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);
        $registry->registerMetricsCollector($collector2);

        $listener = new RequestCounterListener($registry, ['test_route']);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestNonMasterRequest(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);
        $evt->method('isMasterRequest')->willReturn(false);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::never())->method('collectRequest');
        $collector2 = $this->createMock(MetricsCollectorInterface::class);
        $collector2->expects(self::never())->method('collectRequest');

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);
        $registry->registerMetricsCollector($collector2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestExceptionHandlingWithLog(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);
        $evt->method('isMasterRequest')->willReturn(true);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectRequest')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);

        $listener = new RequestCounterListener($registry);
        $logger = new BufferingLogger();
        $listener->setLogger($logger);
        $listener->onKernelRequest($evt);
        $logs = $logger->cleanLogs();
        self::assertCount(1, $logs);
        self::assertCount(3, $logs[0]);
        self::assertEquals('test exception', $logs[0][1]);
    }

    public function testOnKernelRequestExceptionHandlingWithoutLog(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->method('getRequest')->willReturn($request);
        $evt->method('isMasterRequest')->willReturn(true);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectRequest')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelTerminate(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response('', 500);
        $evt = new TerminateEvent($kernel, $request, $response);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectResponse')->with($evt);
        $collector2 = $this->createMock(MetricsCollectorInterface::class);
        $collector2->expects(self::once())->method('collectResponse')->with($evt);

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);
        $registry->registerMetricsCollector($collector2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelTerminate($evt);
    }

    public function testOnKernelTerminateExceptionHandlingWithLog(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = new Response('', 400);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $evt = new TerminateEvent($kernel, $request, $response);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectResponse')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);

        $listener = new RequestCounterListener($registry);
        $logger = new BufferingLogger();
        $listener->setLogger($logger);
        $listener->onKernelTerminate($evt);
        $logs = $logger->cleanLogs();
        self::assertCount(1, $logs);
        self::assertCount(3, $logs[0]);
        self::assertEquals('test exception', $logs[0][1]);
    }

    public function testOnKernelTerminateExceptionHandlingWithoutLog(): void
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = new Response('', 400);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $evt = new TerminateEvent($kernel, $request, $response);

        $collector1 = $this->createMock(MetricsCollectorInterface::class);
        $collector1->expects(self::once())->method('collectResponse')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsCollectorRegistry();
        $registry->registerMetricsCollector($collector1);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelTerminate($evt);
    }
}
