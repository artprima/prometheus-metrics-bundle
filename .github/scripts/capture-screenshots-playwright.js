const { chromium } = require('playwright');
const fs = require('fs');

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function waitForGrafana() {
  console.log('Waiting for Grafana to be ready...');
  for (let i = 0; i < 60; i++) {
    try {
      const response = await fetch('http://localhost:3000/api/health');
      if (response.ok) {
        console.log('Grafana is ready!');
        return true;
      }
    } catch (e) {
      // Grafana not ready yet
    }
    await sleep(2000);
  }
  throw new Error('Grafana failed to start');
}

async function captureScreenshots() {
  await waitForGrafana();

  console.log('Launching Playwright browser...');
  const browser = await chromium.launch({
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-gpu',
      '--disable-web-security'
    ]
  });

  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    // Increase timeouts for slower rendering
    navigationTimeout: 60000,
    timeout: 60000
  });

  const page = await context.newPage();
  
  // Enable request/response logging for debugging
  page.on('request', request => {
    if (request.url().includes('query') || request.url().includes('api')) {
      console.log(`Request: ${request.method()} ${request.url()}`);
    }
  });
  
  page.on('response', response => {
    if (response.url().includes('query') || response.url().includes('api')) {
      console.log(`Response: ${response.status()} ${response.url()}`);
    }
  });

  // Function to capture dashboard with enhanced Playwright features
  async function captureDashboard(dashboardId, name, filename) {
    console.log(`Capturing ${name} dashboard with Playwright...`);
    // Set time range to last 5 minutes and refresh every 5 seconds
    const dashboardUrl = `http://localhost:3000/d/${dashboardId}?orgId=1&refresh=5s&from=now-5m&to=now&kiosk=tv`;
    
    try {
      console.log(`Navigating to: ${dashboardUrl}`);
      await page.goto(dashboardUrl, { waitUntil: 'networkidle', timeout: 45000 });
      
      // Wait for dashboard content - try multiple selectors with Playwright's advanced waiting
      let dashboardLoaded = false;
      const selectors = [
        '.react-grid-layout',
        '.dashboard-container',
        '[data-testid="dashboard-grid"]',
        '.panel-container',
        '.grafana-panel'
      ];
      
      for (const selector of selectors) {
        try {
          await page.waitForSelector(selector, { timeout: 10000, state: 'visible' });
          console.log(`Dashboard loaded with selector: ${selector}`);
          dashboardLoaded = true;
          break;
        } catch (e) {
          console.log(`Selector ${selector} not found, trying next...`);
        }
      }
      
      if (!dashboardLoaded) {
        console.log('Waiting for any dashboard content...');
        await sleep(15000); // Give it more time
      }

      // Wait for all network requests to complete (charts loading)
      console.log('Waiting for network to be idle...');
      await page.waitForLoadState('networkidle');
      
      // Wait for graphs to render - look for canvas or svg elements
      try {
        console.log('Looking for graph elements (canvas/svg)...');
        await page.waitForSelector('canvas, svg', { timeout: 20000 });
        console.log('Graph elements detected');
        
        // Count graph elements for debugging
        const graphCount = await page.evaluate(() => {
          return document.querySelectorAll('canvas, svg').length;
        });
        console.log(`Found ${graphCount} graph elements`);
      } catch (e) {
        console.log('No canvas/svg elements found, checking for other graph indicators...');
        
        // Try to find other Grafana visualization elements
        try {
          await page.waitForSelector('.flot-base, .graph-panel, .panel-content', { timeout: 10000 });
          console.log('Alternative graph elements found');
        } catch (e2) {
          console.log('No graph elements found, proceeding anyway...');
        }
      }

      // Additional wait for JavaScript rendering and DOM updates
      console.log('Waiting for JavaScript rendering...');
      await page.waitForTimeout(10000);

      // Ensure all images are loaded
      console.log('Ensuring all images are loaded...');
      await page.evaluate(() => {
        return Promise.all([...document.querySelectorAll('img')].map(img => {
          if (img.complete) return Promise.resolve();
          return new Promise(resolve => {
            img.addEventListener('load', resolve);
            img.addEventListener('error', resolve);
          });
        }));
      });

      // Wait for any animations to complete
      console.log('Waiting for animations to complete...');
      await page.waitForTimeout(3000);

      // Debug: Check what's actually on the page
      const pageTitle = await page.title();
      console.log(`Page title: ${pageTitle}`);
      
      const panelCount = await page.evaluate(() => {
        return document.querySelectorAll('.panel-container, .grafana-panel').length;
      });
      console.log(`Found ${panelCount} panels on the page`);
      
      // Take screenshot with high quality settings
      console.log(`Taking screenshot: ${filename}`);
      await page.screenshot({
        path: `screenshots/${filename}`,
        fullPage: true,
        quality: 100,
        type: 'png'
      });
      
      console.log(`Screenshot saved: ${filename}`);
      
      // Verify screenshot was created and get file size
      try {
        const stats = fs.statSync(`screenshots/${filename}`);
        console.log(`Screenshot file size: ${stats.size} bytes`);
      } catch (e) {
        console.error(`Failed to verify screenshot file: ${e.message}`);
      }
      
    } catch (error) {
      console.error(`Error capturing ${name}: ${error.message}`);
      console.error(`Error stack: ${error.stack}`);
      
      // Try to take a screenshot anyway for debugging
      try {
        console.log('Attempting to take error screenshot for debugging...');
        await page.screenshot({
          path: `screenshots/${filename.replace('.png', '-error.png')}`,
          fullPage: true
        });
        console.log(`Error screenshot saved for debugging: ${filename.replace('.png', '-error.png')}`);
      } catch (e) {
        console.error('Failed to take error screenshot');
      }
    }
  }

  try {
    // Login to Grafana
    console.log('Logging into Grafana...');
    await page.goto('http://localhost:3000/login');
    await page.waitForSelector('input[name="user"]', { timeout: 30000 });
    await page.fill('input[name="user"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('button[type="submit"]');

    // Skip password change if prompted
    try {
      await page.waitForSelector('button[aria-label="Skip"]', {
        timeout: 5000
      });
      await page.click('button[aria-label="Skip"]');
      console.log('Skipped password change');
    } catch (e) {
      console.log('No password change prompt found');
    }

    // Wait for main page to load
    try {
      await page.waitForSelector('[data-testid="data-testid Nav"]', { timeout: 15000 });
      console.log('Grafana navigation loaded');
    } catch (e) {
      console.log('Navigation selector not found, using alternative loading method...');
      await sleep(10000);
    }

    // Create screenshots directory
    if (!fs.existsSync('screenshots')) {
      fs.mkdirSync('screenshots');
    }

    console.log('Waiting 5 minutes for metrics to accumulate before taking screenshots...');
    await sleep(300000); // Wait 5 minutes (300,000 milliseconds)
    console.log('5 minute wait completed, proceeding with screenshots...');

    // Capture both dashboards
    await captureDashboard(
      'symfony-app-overview',
      'Symfony Application Overview',
      'symfony-app-overview-dashboard-live-playwright.png'
    );
    
    await captureDashboard(
      'symfony-app-monitoring', 
      'Symfony Application Monitoring',
      'symfony-app-monitoring-dashboard-live-playwright.png'
    );

  } catch (error) {
    console.error(`Fatal error during screenshot capture: ${error.message}`);
    console.error(`Error stack: ${error.stack}`);
    throw error;
  } finally {
    console.log('Closing browser...');
    await browser.close();
    console.log('Playwright screenshot capture completed!');
  }
}

captureScreenshots().catch(console.error);