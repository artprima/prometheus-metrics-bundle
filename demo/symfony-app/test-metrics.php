#!/usr/bin/env php
<?php

declare(strict_types=1);

// Simple test to demonstrate the metrics exposed by the bundle

require_once dirname(__DIR__).'/vendor/autoload.php';

use Artprima\PrometheusMetricsBundle\Metrics\AppMetrics;
use Artprima\PrometheusMetricsBundle\Metrics\LabelResolver;
use Artprima\PrometheusMetricsBundle\Metrics\Renderer;
use Prometheus\CollectorRegistry;
use Prometheus\Histogram;
use Prometheus\Storage\InMemory;

echo "🔍 Testing Prometheus Metrics Bundle\n";
echo "=====================================\n\n";

// Initialize the metric system
$storage = new InMemory();
$registry = new CollectorRegistry($storage);
$renderer = new Renderer($registry, 'symfony');

$labelResolver = new LabelResolver([]);

// Initialize AppMetrics
$appMetrics = new AppMetrics($labelResolver, Histogram::getDefaultBuckets());
$appMetrics->init('symfony', $registry);

echo "✅ Metrics system initialized\n";

// Simulate some requests
echo "\n📊 Simulating HTTP requests...\n";

// Simulate different types of requests
$requestTypes = [
    ['method' => 'GET', 'route' => 'home', 'status' => 200],
    ['method' => 'GET', 'route' => 'api_users', 'status' => 200],
    ['method' => 'POST', 'route' => 'api_create', 'status' => 201],
    ['method' => 'GET', 'route' => 'api_error', 'status' => 500],
    ['method' => 'GET', 'route' => 'api_not_found', 'status' => 404],
];

foreach ($requestTypes as $i => $requestType) {
    echo '  Request '.($i + 1).": {$requestType['method']} /{$requestType['route']} -> {$requestType['status']}\n";

    // Create mock request
    $request = new Symfony\Component\HttpFoundation\Request();
    $request->setMethod($requestType['method']);
    $request->attributes->set('_route', $requestType['route']);
    $request->server->set('HOSTNAME', 'demo-server');

    // Create events
    $requestEvent = new Symfony\Component\HttpKernel\Event\RequestEvent(
        new Symfony\Component\HttpKernel\HttpKernel(
            new Symfony\Component\EventDispatcher\EventDispatcher(),
            new Symfony\Component\HttpKernel\Controller\ControllerResolver()
        ),
        $request,
        Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST
    );

    $response = new Symfony\Component\HttpFoundation\Response('', $requestType['status']);
    $responseEvent = new Symfony\Component\HttpKernel\Event\ResponseEvent(
        new Symfony\Component\HttpKernel\HttpKernel(
            new Symfony\Component\EventDispatcher\EventDispatcher(),
            new Symfony\Component\HttpKernel\Controller\ControllerResolver()
        ),
        $request,
        Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    // Collect metrics
    $appMetrics->collectStart($requestEvent);
    usleep(rand(10000, 100000)); // Simulate request duration
    $appMetrics->collectRequest($requestEvent);
    $appMetrics->collectResponse($responseEvent);
}

echo "\n📈 Generated metrics output:\n";
echo "==============================\n";

// Get the metrics output
$metricsOutput = $renderer->renderResponse()->getContent();
echo $metricsOutput;

echo "\n✅ Demo completed successfully!\n";
echo "\nThe metrics above show the format that Prometheus scrapes.\n";
echo "The Grafana dashboards visualize these metrics in user-friendly charts.\n";
