<?php

namespace Tests\Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\EventListener\RequestCounterListener;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsGeneratorRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class RequestCounterListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt->expects(self::any())->method('isMasterRequest')->willReturn(true);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectRequest')->with($evt);
        $generator2 = $this->createMock(MetricsGeneratorInterface::class);
        $generator2->expects(self::once())->method('collectRequest')->with($evt);

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);
        $registry->registerMetricsGenerator($generator2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestIgnoredRoute()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt->expects(self::any())->method('isMasterRequest')->willReturn(true);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::never())->method('collectRequest');
        $generator2 = $this->createMock(MetricsGeneratorInterface::class);
        $generator2->expects(self::never())->method('collectRequest');

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);
        $registry->registerMetricsGenerator($generator2);

        $listener = new RequestCounterListener($registry, ['test_route']);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestNonMasterRequest()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt->expects(self::any())->method('isMasterRequest')->willReturn(false);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::never())->method('collectRequest');
        $generator2 = $this->createMock(MetricsGeneratorInterface::class);
        $generator2->expects(self::never())->method('collectRequest');

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);
        $registry->registerMetricsGenerator($generator2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelRequestExceptionHandlingWithLog()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt->expects(self::any())->method('isMasterRequest')->willReturn(true);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectRequest')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);

        $listener = new RequestCounterListener($registry);
        $logger = new BufferingLogger();
        $listener->setLogger($logger);
        $listener->onKernelRequest($evt);
        $logs = $logger->cleanLogs();
        $this->assertCount(1, $logs);
        $this->assertCount(3, $logs[0]);
        $this->assertEquals('test exception', $logs[0][1]);
    }

    public function testOnKernelRequestExceptionHandlingWithoutLog()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(RequestEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);
        $evt->expects(self::any())->method('isMasterRequest')->willReturn(true);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectRequest')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelRequest($evt);
    }

    public function testOnKernelTerminate()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(TerminateEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectResponse')->with($evt);
        $generator2 = $this->createMock(MetricsGeneratorInterface::class);
        $generator2->expects(self::once())->method('collectResponse')->with($evt);

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);
        $registry->registerMetricsGenerator($generator2);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelTerminate($evt);
    }

    public function testOnKernelTerminateExceptionHandlingWithLog()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(TerminateEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectResponse')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);

        $listener = new RequestCounterListener($registry);
        $logger = new BufferingLogger();
        $listener->setLogger($logger);
        $listener->onKernelTerminate($evt);
        $logs = $logger->cleanLogs();
        $this->assertCount(1, $logs);
        $this->assertCount(3, $logs[0]);
        $this->assertEquals('test exception', $logs[0][1]);
    }

    public function testOnKernelTerminateExceptionHandlingWithoutLog()
    {
        $request = new Request([], [], ['_route' => 'test_route'], [], [], ['REQUEST_METHOD' => 'GET']);
        $evt = $this->createMock(TerminateEvent::class);
        $evt->expects(self::any())->method('getRequest')->willReturn($request);

        $generator1 = $this->createMock(MetricsGeneratorInterface::class);
        $generator1->expects(self::once())->method('collectResponse')->willThrowException(new \Exception('test exception'));

        $registry = new MetricsGeneratorRegistry();
        $registry->registerMetricsGenerator($generator1);

        $listener = new RequestCounterListener($registry);
        $listener->onKernelTerminate($evt);
    }
}
