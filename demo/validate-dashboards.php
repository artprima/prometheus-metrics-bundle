#!/usr/bin/env php
<?php

// Simple validation script for Grafana dashboard JSON files

$dashboardsDir = dirname(__DIR__) . '/grafana';
$dashboards = [
    'symfony-app-overview.json',
    'symfony-app-monitoring.json'
];

$errors = [];

foreach ($dashboards as $dashboard) {
    $file = $dashboardsDir . '/' . $dashboard;
    
    if (!file_exists($file)) {
        $errors[] = "Dashboard file $dashboard not found";
        continue;
    }
    
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid JSON in $dashboard: " . json_last_error_msg();
        continue;
    }
    
    // Validate basic dashboard structure
    $requiredFields = ['panels', 'title'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $errors[] = "Missing required field '$field' in $dashboard";
        }
    }
    
    if (!isset($data['panels']) || !is_array($data['panels'])) {
        $errors[] = "No panels found in $dashboard";
        continue;
    }
    
    // Validate that panels have required prometheus metrics (with template variables)
    $expectedMetrics = [
        'http_requests_total',
        'request_durations_histogram_seconds',
        'http_2xx_responses_total', 
        'http_4xx_responses_total',
        'http_5xx_responses_total'
    ];
    
    $foundMetrics = [];
    foreach ($data['panels'] as $panel) {
        if (isset($panel['targets'])) {
            foreach ($panel['targets'] as $target) {
                if (isset($target['expr'])) {
                    foreach ($expectedMetrics as $metric) {
                        if (strpos($target['expr'], $metric) !== false) {
                            $foundMetrics[] = $metric;
                        }
                    }
                }
            }
        }
    }
    
    $missingMetrics = array_diff($expectedMetrics, $foundMetrics);
    if (!empty($missingMetrics)) {
        $errors[] = "Dashboard $dashboard is missing metrics: " . implode(', ', $missingMetrics);
    }
    
    echo "✓ Dashboard $dashboard validated successfully\n";
}

if (!empty($errors)) {
    echo "\nErrors found:\n";
    foreach ($errors as $error) {
        echo "✗ $error\n";
    }
    exit(1);
}

echo "\n✓ All dashboards validated successfully!\n";
exit(0);