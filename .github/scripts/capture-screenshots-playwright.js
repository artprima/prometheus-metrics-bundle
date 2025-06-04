const { chromium } = require('playwright');
const fs = require('fs');

// Add fetchif not available (Node 18+ has it built-in)
const fetch = globalThis.fetch || require('node-fetch');

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function waitForGrafana() {
  console.log('Waiting for Grafana to be ready...');
  for (let i = 0; i < 90; i++) { // Increased attempts
    try {
      const response = await fetch('http://localhost:3000/api/health');
      if (response.ok) {
        console.log('Grafana health API is ready!');
        
        return true;
      }
    } catch (e) {
      // Grafana not ready yet
    }
    console.log(`Attempt ${i + 1}/90 - waiting for Grafana...`);
    await sleep(3000); // Increased wait between attempts
  }
  throw new Error('Grafana failed to start properly');
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
  
  // Create screenshots directory early
  if (!fs.existsSync('screenshots')) {
    fs.mkdirSync('screenshots');
  }
  
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
    // Login to Grafana with robust error handling and retry logic
    console.log('Logging into Grafana...');
    
    let loginSuccess = false;
    let attempts = 0;
    const maxAttempts = 3;
    
    while (!loginSuccess && attempts < maxAttempts) {
      attempts++;
      console.log(`Login attempt ${attempts}/${maxAttempts}`);
      
      try {
        await page.goto('http://localhost:3000/login', { 
          waitUntil: 'networkidle',
          timeout: 60000 
        });
        
        // Debug: Check what's on the page
        console.log('Checking login page content...');
        const pageTitle = await page.title();
        console.log(`Login page title: ${pageTitle}`);
        
        // Get page content for debugging
        const bodyContent = await page.evaluate(() => {
          return document.body ? document.body.innerText.substring(0, 800) : 'No body found';
        });
        console.log(`Page content preview: ${bodyContent}`);
        
        // Check if it's the Grafana frontend error page
        if (bodyContent.includes('failed to load its application files')) {
          console.error('Grafana frontend failed to load - taking debug screenshot');
          await page.screenshot({
            path: `screenshots/grafana-frontend-error-attempt-${attempts}.png`,
            fullPage: true
          });
          
          if (attempts < maxAttempts) {
            console.log(`Grafana frontend error, waiting 30 seconds before retry...`);
            await sleep(30000);
            continue;
          } else {
            throw new Error('Grafana frontend failed to load its application files after multiple attempts.');
          }
        }
        
        // Check page HTML for better debugging
        const pageHTML = await page.evaluate(() => {
          return document.documentElement.innerHTML.substring(0, 2000);
        });
        console.log(`Page HTML preview: ${pageHTML.substring(0, 500)}...`);
        
        // Try multiple selectors for the login form
        let loginFormFound = false;
        const userInputSelectors = [
          'input[name="user"]',
          'input[placeholder*="email"]',
          'input[placeholder*="username"]',
          'input[type="text"]',
          '.login-form input[type="text"]',
          '[data-testid="data-testid Username input"]'
        ];
        
        for (const selector of userInputSelectors) {
          try {
            console.log(`Trying selector: ${selector}`);
            await page.waitForSelector(selector, { timeout: 10000 });
            console.log(`Found username input with selector: ${selector}`);
            await page.fill(selector, 'admin');
            loginFormFound = true;
            break;
          } catch (e) {
            console.log(`Selector ${selector} not found: ${e.message}`);
          }
        }
        
        if (!loginFormFound) {
          // Take a screenshot for debugging
          await page.screenshot({
            path: `screenshots/login-page-debug-attempt-${attempts}.png`,
            fullPage: true
          });
          
          if (attempts < maxAttempts) {
            console.log('Login form not found, waiting before retry...');
            await sleep(20000);
            continue;
          } else {
            throw new Error('Could not find username input field after multiple attempts');
          }
        }
        
        // Find and fill password field
        const passwordSelectors = [
          'input[name="password"]',
          'input[type="password"]',
          '.login-form input[type="password"]',
          '[data-testid="data-testid Password input"]'
        ];
        
        let passwordFound = false;
        for (const selector of passwordSelectors) {
          try {
            console.log(`Trying password selector: ${selector}`);
            await page.waitForSelector(selector, { timeout: 5000 });
            console.log(`Found password input with selector: ${selector}`);
            await page.fill(selector, 'admin');
            passwordFound = true;
            break;
          } catch (e) {
            console.log(`Password selector ${selector} not found: ${e.message}`);
          }
        }
        
        if (!passwordFound) {
          if (attempts < maxAttempts) {
            console.log('Password field not found, waiting before retry...');
            await sleep(20000);
            continue;
          } else {
            throw new Error('Could not find password input field after multiple attempts');
          }
        }
        
        // Find and click submit button
        const submitSelectors = [
          'button[type="submit"]',
          'button:has-text("Log in")',
          'button:has-text("Sign in")',
          '.login-form button',
          '[data-testid="data-testid Login button"]'
        ];
        
        let submitClicked = false;
        for (const selector of submitSelectors) {
          try {
            console.log(`Trying submit selector: ${selector}`);
            await page.waitForSelector(selector, { timeout: 5000 });
            console.log(`Found submit button with selector: ${selector}`);
            await page.click(selector);
            submitClicked = true;
            break;
          } catch (e) {
            console.log(`Submit selector ${selector} not found: ${e.message}`);
          }
        }
        
        if (!submitClicked) {
          if (attempts < maxAttempts) {
            console.log('Submit button not found, waiting before retry...');
            await sleep(20000);
            continue;
          } else {
            throw new Error('Could not find submit button after multiple attempts');
          }
        }
        
        console.log('Login form submitted successfully');
        loginSuccess = true;
        
      } catch (error) {
        console.error(`Login attempt ${attempts} failed: ${error.message}`);
        if (attempts >= maxAttempts) {
          throw error;
        }
        console.log('Waiting before retry...');
        await sleep(20000);
      }
    }

    // Skip password change if prompted
    try {
      console.log('Checking for password change prompt...');
      const skipSelectors = [
        'button[aria-label="Skip"]',
        'button:has-text("Skip")',
        'a:has-text("Skip")',
        '[data-testid="data-testid Skip change password button"]'
      ];
      
      let skipped = false;
      for (const selector of skipSelectors) {
        try {
          await page.waitForSelector(selector, { timeout: 5000 });
          await page.click(selector);
          console.log(`Skipped password change with selector: ${selector}`);
          skipped = true;
          break;
        } catch (e) {
          console.log(`Skip selector ${selector} not found`);
        }
      }
      
      if (!skipped) {
        console.log('No password change prompt found');
      }
    } catch (e) {
      console.log('Error handling password change prompt:', e.message);
    }

    // Wait for main page to load with multiple approaches
    console.log('Waiting for Grafana main page...');
    try {
      const navSelectors = [
        '[data-testid="data-testid Nav"]',
        '.sidemenu',
        '.navbar',
        '.main-view',
        '[aria-label="Grafana"]'
      ];
      
      let navLoaded = false;
      for (const selector of navSelectors) {
        try {
          await page.waitForSelector(selector, { timeout: 10000 });
          console.log(`Grafana navigation loaded with selector: ${selector}`);
          navLoaded = true;
          break;
        } catch (e) {
          console.log(`Nav selector ${selector} not found`);
        }
      }
      
      if (!navLoaded) {
        console.log('Using alternative loading method...');
        await sleep(15000); // Give more time for loading
      }
    } catch (e) {
      console.log('Error waiting for navigation:', e.message);
      await sleep(15000);
    }

    // Wait for metrics to accumulate
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