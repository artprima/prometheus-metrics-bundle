<?php

namespace Tests\Artprima\PrometheusMetricsBundle\Controller;

use Artprima\PrometheusMetricsBundle\Controller\MetricsController;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MetricsControllerTest extends TestCase
{
    public function testPrometheus()
    {
        $response = new Response();
        $renderer = $this->createMock(Renderer::class);
        $renderer->expects(self::once())->method('renderResponse')->willReturn($response);
        $controller = new MetricsController($renderer);
        $result = $controller->prometheus();
        $this->assertSame($response, $result);
    }
}
