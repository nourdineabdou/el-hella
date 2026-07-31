// @ts-check
const { defineConfig, devices } = require('@playwright/test');
const fs = require('fs');

const localChrome = 'C:/Users/KTB i3 Good One/AppData/Local/ms-playwright/chromium-1228/chrome-win64/chrome.exe';
const executablePath = fs.existsSync(localChrome) ? localChrome : undefined;

module.exports = defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    retries: 1,
    timeout: 45000,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'tests/e2e/report' }]],
    outputDir: 'tests/e2e/results',
    use: {
        baseURL: 'http://el-hella.test',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        launchOptions: executablePath ? { executablePath } : {},
    },
    projects: [
        {
            name: 'android-phone-360x800',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 360, height: 800 },
                userAgent: 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36',
                hasTouch: true,
                isMobile: true,
            },
        },
        {
            name: 'iphone-390x844',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 390, height: 844 },
                userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                hasTouch: true,
                isMobile: true,
            },
        },
        {
            name: 'tablet-768x1024',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 768, height: 1024 },
                hasTouch: true,
                isMobile: true,
            },
        },
        {
            name: 'desktop-1440x900',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 900 },
            },
        },
    ],
});
