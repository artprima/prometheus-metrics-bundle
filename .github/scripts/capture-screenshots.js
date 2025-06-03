const puppeteer = require('puppeteer');
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

  const browser = await puppeteer.launch({
    headless: 'new',
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-gpu',
      '--disable-web-security',
      '--disable-features=VizDisplayCompositor'
    ]
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 1080 });

  // Set longer timeouts
  page.setDefaultTimeout(60000);
  page.setDefaultNavigationTimeout(60000);

  // Login to Grafana
  console.log('Logging into Grafana...');
  await page.goto('http://localhost:3000/login');
  await page.waitForSelector('input[name="user"]', { timeout: 30000 });
  await page.type('input[name="user"]', 'admin');
  await page.type('input[name="password"]', 'admin');
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
    console.log('Alternative loading method...');
    await sleep(10000);
  }

  // Create screenshots directory
  if (!fs.existsSync('screenshots')) {
    fs.mkdirSync('screenshots');
  }

  // Function to capture dashboard with retry logic
  async function captureDashboard(dashboardId, name, filename) {
    console.log(`Capturing ${name} dashboard...`);
    const dashboardUrl = `http://localhost:3000/d/${dashboardId}?orgId=1&refresh=5s&kiosk=tv`;
    
    try {
      await page.goto(dashboardUrl, { waitUntil: 'networkidle0', timeout: 45000 });
      
      // Wait for dashboard content - try multiple selectors
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
          await page.waitForSelector(selector, { timeout: 10000 });
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
      
      // Wait additional time for data to load
      await sleep(10000);
      
      // Take screenshot
      await page.screenshot({
        path: `screenshots/${filename}`,
        fullPage: true
      });
      
      console.log(`Screenshot saved: ${filename}`);
      
    } catch (error) {
      console.error(`Error capturing ${name}: ${error.message}`);
      // Try to take a screenshot anyway for debugging
      try {
        await page.screenshot({
          path: `screenshots/${filename.replace('.png', '-error.png')}`,
          fullPage: true
        });
      } catch (e) {
        console.error('Failed to take error screenshot');
      }
    }
  }

  // Capture both dashboards
  await captureDashboard(
    'symfony-app-overview',
    'Symfony Application Overview',
    'symfony-app-overview-dashboard-live.png'
  );
  
  await captureDashboard(
    'symfony-app-monitoring', 
    'Symfony Application Monitoring',
    'symfony-app-monitoring-dashboard-live.png'
  );

  await browser.close();
  console.log('Screenshot capture completed!');
}

captureScreenshots().catch(console.error);