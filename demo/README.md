# Dashboard Verification Demo

This directory contains a verification setup for the Grafana dashboards provided with the prometheus-metrics-bundle.

## What's Included

### Dashboard Validation

- **validate-dashboards.php**: Validates the JSON structure and ensures required metrics are present
- Checks for proper Grafana dashboard format
- Verifies that all expected Prometheus metrics are referenced

### Docker Demo Environment

- **docker-compose.yml**: Complete demo stack including:
  - Symfony application with the bundle installed
  - Prometheus server configured to scrape metrics
  - Grafana with dashboards automatically provisioned
  - Activity simulator for continuous traffic generation

### How to Run the Demo

1. **Validate Dashboards**:
   ```bash
   php validate-dashboards.php
   ```

2. **Run Complete Demo Stack**:
   ```bash
   docker compose up --build
   ```
   
   Then access:
   - Symfony app: http://localhost:8080
   - Prometheus: http://localhost:9090
   - Grafana: http://localhost:3000 (admin/admin)
   
   **Note**: The Symfony app is fully containerized with all dependencies installed during the Docker build process.

3. **Automatic Traffic Generation**:
   The `activity-simulator` container automatically generates traffic to populate the metrics dashboards with realistic data:
   - Makes requests to various endpoints (`/`, `/api/users`, `/api/error`, `/api/slow`, `/health`)
   - Randomized request patterns and delays
   - Runs continuously to show dynamic data in Grafana

4. **Manual Testing**:
   ```bash
   # Generate additional test traffic manually
   curl http://localhost:8080/
   curl http://localhost:8080/api/users
   curl http://localhost:8080/api/error
   curl http://localhost:8080/api/slow
   ```

## Configuration

### Bundle Configuration
- **Storage Type**: Uses APCu for persistent metrics storage across requests
- **Namespace**: `symfony` (configurable via template variables)
- **Endpoints**: All demo endpoints include metrics collection

### Controller Services
- Controllers are properly configured as service subscribers
- Full Symfony DI container integration
- All bundle event listeners active

## Dashboard Features Verified

### Symfony Application Overview Dashboard
- ✅ HTTP request rate monitoring
- ✅ Response status code distribution (2xx, 3xx, 4xx, 5xx)
- ✅ Response time percentiles (50th, 95th, 99th)
- ✅ Exception tracking
- ✅ Application metadata display

### Symfony Application Monitoring Dashboard  
- ✅ Key performance indicators
- ✅ Error rate percentage tracking
- ✅ Health status overview
- ✅ Instance and version information
- ✅ PHP environment details

## Metrics Coverage

The dashboards utilize all the default metrics provided by the bundle:

- `{namespace}_http_requests_total` - Total HTTP requests
- `{namespace}_http_*xx_responses_total` - Response counts by status code
- `{namespace}_request_durations_histogram_seconds` - Request duration histograms
- `{namespace}_instance_name` - Application instance identifier
- `{namespace}_app_version` - Application version
- `{namespace}_exception` - Exception counters

## Template Variables

Both dashboards support these template variables:
- **$datasource** - Prometheus data source selection
- **$namespace** - Metric namespace (defaults to "symfony")
- **$job** - Prometheus job name for filtering

This allows the dashboards to be used in different environments without modification.
