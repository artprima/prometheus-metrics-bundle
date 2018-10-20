<?php

namespace Artprima\PrometheusMetricsBundle\Tests\Controller;

use Artprima\PrometheusMetricsBundle\Controller\MetricsController;
use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MetricsControllerTest extends TestCase
{
    public function testPrometheus()
    {
        $response = new Response();
        $appMetrics = $this->createMock(AppMetrics::class);
        $appMetrics->expects(self::once())->method('renderResponse')->willReturn($response);
        $controller = new MetricsController($appMetrics);
        $result = $controller->prometheus();
        $this->assertSame($response, $result);
    }
}