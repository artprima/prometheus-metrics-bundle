# Grafana Dashboards

This directory contains example Grafana dashboard configurations for visualizing the Prometheus metrics exposed by this bundle.

## Available Dashboards

1. **symfony-app-overview.json** - Main application overview dashboard showing HTTP requests, responses, and performance metrics
2. **symfony-app-monitoring.json** - Application monitoring dashboard for exceptions, errors, and system health

## Usage

1. Import the JSON files into your Grafana instance
2. Configure your Prometheus data source to scrape metrics from your Symfony application's `/metrics/prometheus` endpoint
3. Customize the dashboards as needed for your specific monitoring requirements

## Metrics Covered

These dashboards visualize the following metrics from the prometheus-metrics-bundle:

- HTTP request/response rates and status codes
- Request duration histograms and percentiles
- Exception monitoring by class
- Application instance information
- Custom labels (if configured)