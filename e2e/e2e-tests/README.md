# E2E Tests with Playwright

This directory contains end-to-end tests for capturing Grafana dashboard screenshots using Playwright.

## Prerequisites

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Install Playwright browsers:**
   ```bash
   npm run test:e2e:install
   ```

3. **Install system dependencies (if needed):**
   ```bash
   npm run test:e2e:install-deps
   ```

## Environment Setup

Before running the tests, make sure you have:

1. **Grafana running** on `http://localhost:3000`
2. **Default admin credentials** (admin/admin)
3. **Required dashboards** imported:
   - `symfony-app-overview`
   - `symfony-app-monitoring`

## Running Tests

### Basic Commands

```bash
# Run all e2e tests (headless)
npm run test:e2e

# Run tests with browser UI visible
npm run test:e2e:headed

# Run tests with Playwright UI (interactive mode)
npm run test:e2e:ui

# Run tests in debug mode (step-by-step)
npm run test:e2e:debug
```

### Browser-Specific Tests

```bash
# Run tests only in Chromium
npm run test:e2e:chromium

# Run tests only in Firefox
npm run test:e2e:firefox

# Run tests only in WebKit (Safari)
npm run test:e2e:webkit
```

### Advanced Options

```bash
# Run specific test file
npx playwright test grafana-screenshots.spec.js

# Run tests with specific grep pattern
npx playwright test --grep "Overview"

# Run tests with maximum workers
npx playwright test --workers=1

# Run tests with specific timeout
npx playwright test --timeout=600000
```

## Test Reports

```bash
# View HTML test report
npm run test:e2e:report
```

## Test Structure

The main test file `grafana-screenshots.spec.js` contains:

1. **Setup functions:**
   - `waitForGrafana()` - Waits for Grafana health endpoint
   - `loginToGrafana()` - Handles Grafana login with retry logic

2. **Test cases:**
   - Login verification and metrics accumulation wait
   - Symfony Application Overview dashboard screenshot
   - Symfony Application Monitoring dashboard screenshot

3. **Screenshot capture:**
   - High-quality PNG screenshots
   - Full-page capture
   - Error handling with debug screenshots
   - File size verification

## Configuration

The Playwright configuration is in `playwright.config.js` and includes:

- **Timeout:** 10 minutes per test (for long-running screenshot operations)
- **Retries:** 2 retries on CI, 0 locally
- **Browsers:** Chromium, Firefox, WebKit
- **Viewport:** 1920x1080
- **Screenshots:** On failure
- **Videos:** On failure
- **Traces:** On first retry

## Output

Screenshots are saved to the `screenshots/` directory:
- `symfony-app-overview-dashboard-live-playwright.png`
- `symfony-app-monitoring-dashboard-live-playwright.png`
- Error screenshots (if any): `*-error.png`

## Troubleshooting

1. **Grafana not ready:**
   - Ensure Grafana is running on port 3000
   - Check Grafana health endpoint: `http://localhost:3000/api/health`

2. **Login issues:**
   - Verify admin/admin credentials work
   - Check for password change prompts

3. **Dashboard not found:**
   - Ensure dashboards are imported with correct IDs
   - Check dashboard URLs manually

4. **Timeout issues:**
   - Increase timeout in `playwright.config.js`
   - Check network connectivity
   - Verify dashboard loading performance

## Migration from Original Script

This Playwright test setup replaces the original `.github/scripts/capture-screenshots-playwright.js` script with:

- ✅ Proper test structure and assertions
- ✅ Better error handling and debugging
- ✅ Multiple browser support
- ✅ Configurable timeouts and retries
- ✅ HTML reports and traces
- ✅ CI/CD integration ready
- ✅ Interactive debugging modes
