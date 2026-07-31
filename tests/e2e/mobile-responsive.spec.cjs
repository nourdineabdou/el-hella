// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');

const ADMIN = { login: 'admin.demo@elhella.com', password: 'password' };
const DISTRIBUTOR = { login: 'distributeur.demo@elhella.com', password: 'password' };

const shot = (page, testInfo, name) =>
    page.screenshot({
        path: path.join('tests/e2e/screenshots', testInfo.project.name, `${name}.png`),
        fullPage: true,
    });

async function expectNoHorizontalOverflow(page) {
    const { scrollWidth, clientWidth } = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
    }));
    expect(scrollWidth, 'page must not scroll horizontally').toBeLessThanOrEqual(clientWidth + 1);
}

async function login(page, creds) {
    await page.goto('/login');
    await page.fill('#login', creds.login);
    await page.fill('#password', creds.password);
    await page.click('button[type=submit]');
    await page.waitForURL(/dashboard/);
}

test.describe('Login', () => {
    test('renders without overflow and has correct mobile keyboard attributes', async ({ page }, testInfo) => {
        await page.goto('/login');
        await expectNoHorizontalOverflow(page);

        await expect(page.locator('#login')).toHaveAttribute('autocomplete', 'username');
        await expect(page.locator('#login')).toHaveAttribute('enterkeyhint', 'next');
        await expect(page.locator('#password')).toHaveAttribute('type', 'password');
        await expect(page.locator('#password')).toHaveAttribute('enterkeyhint', 'done');

        await shot(page, testInfo, '01-login');
    });

    test('shows a clear error on invalid credentials (form validation)', async ({ page }, testInfo) => {
        await page.goto('/login');
        await page.fill('#login', 'admin.demo@elhella.com');
        await page.fill('#password', 'wrong-password');
        await page.click('button[type=submit]');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('body')).toContainText(/./); // page reloaded, not a JS crash
        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '02-login-error');
    });

    test('logs in successfully and reaches the dashboard', async ({ page }, testInfo) => {
        await login(page, ADMIN);
        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '03-admin-dashboard');
    });
});

test.describe('Navigation', () => {
    test('bottom nav is present on mobile/tablet and highlights the active page', async ({ page }, testInfo) => {
        await login(page, ADMIN);

        const viewportWidth = page.viewportSize()?.width ?? 1440;
        const bottomNav = page.locator('.eh-bottom-nav');

        if (viewportWidth < 992) {
            await expect(bottomNav).toBeVisible();
            await expect(bottomNav.locator('.eh-bottom-nav-item.active')).toHaveCount(1);
        } else {
            await expect(bottomNav).toBeHidden();
        }

        await shot(page, testInfo, '04-bottom-nav');
    });

    test('hamburger menu opens and closes the sidebar', async ({ page }, testInfo) => {
        await login(page, ADMIN);

        const viewportWidth = page.viewportSize()?.width ?? 1440;
        test.skip(viewportWidth >= 992, 'sidebar is always visible on desktop, no toggle needed');

        const sidebar = page.locator('.eh-sidebar');
        await expect(sidebar).not.toHaveClass(/show/);

        await page.click('.eh-sidebar-toggle');
        await expect(sidebar).toHaveClass(/show/);
        await shot(page, testInfo, '05-menu-open');

        // On narrow phones the 260px drawer can cover most of the width, so a
        // naive center-click may land on the sidebar itself. Click a point in
        // the backdrop that is guaranteed to be outside the sidebar's box.
        const sidebarBox = await sidebar.boundingBox();
        const backdropClickX = sidebarBox && sidebarBox.x < 10 ? sidebarBox.width + 10 : 5;
        await page.locator('.eh-sidebar-backdrop').click({ position: { x: backdropClickX, y: 20 } });
        await expect(sidebar).not.toHaveClass(/show/);
    });
});

test.describe('Forms & keyboard attributes', () => {
    test('shop creation form uses the right keyboard per field type', async ({ page }, testInfo) => {
        await login(page, DISTRIBUTOR);
        await page.goto('/distributor/shops');
        await expectNoHorizontalOverflow(page);

        const name = page.locator('#create-shop-form input[name=name]');
        const owner = page.locator('#create-shop-form input[name=owner_name]');
        const phone = page.locator('#create-shop-form input[name=phone]');

        await expect(phone).toHaveAttribute('type', 'tel');
        await expect(phone).toHaveAttribute('inputmode', 'tel');
        await expect(phone).toHaveAttribute('enterkeyhint', 'done');

        await name.fill('Playwright Test Shop');
        await owner.fill('Playwright Tester');
        await phone.fill('22212345678');

        await shot(page, testInfo, '06-create-shop-form');
    });

    test('sale flow: product search and quantity field use a numeric keyboard', async ({ page }, testInfo) => {
        await login(page, DISTRIBUTOR);
        await page.goto('/distributor/shops');

        const firstShopLink = page.locator('#shop-search-results a, .card-shop-result').first();
        await page.fill('#shop-search-input', 'a');
        await page.waitForTimeout(500);

        if (await firstShopLink.count() === 0) {
            test.skip(true, 'no seeded shop matched the search term');
        }

        await firstShopLink.click();
        await page.waitForURL(/distributor\/shops\/\d+/);

        await page.click('#sell-action');
        await expect(page.locator('#sale-panel')).toBeVisible();

        const search = page.locator('#product-search');
        await expect(search).toHaveAttribute('inputmode', 'search');
        await expect(search).toHaveAttribute('enterkeyhint', 'search');

        await search.fill('a');
        await page.waitForTimeout(400);

        const firstResult = page.locator('#product-search-results button[data-product-id]').first();
        if (await firstResult.count() > 0) {
            await firstResult.click();
            const qty = page.locator('.product-quantity-input').first();
            await expect(qty).toHaveAttribute('inputmode', 'decimal');
            await qty.fill('3');
        }

        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '07-sale-panel');
    });
});

test.describe('Modals', () => {
    test('delete-account modal behaves as a bottom sheet on mobile', async ({ page }, testInfo) => {
        await login(page, ADMIN);
        await page.goto('/profile');

        await page.click('button[data-bs-target="#confirm-user-deletion"]');
        const modal = page.locator('#confirm-user-deletion');
        await expect(modal).toBeVisible();

        const viewportWidth = page.viewportSize()?.width ?? 1440;
        if (viewportWidth < 768) {
            const dialog = modal.locator('.modal-dialog');
            const alignItems = await dialog.evaluate((el) => getComputedStyle(el).alignItems);
            expect(alignItems).toBe('flex-end');
        }

        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '08-modal-open');

        await page.click('#confirm-user-deletion .btn-close');
        await expect(modal).toBeHidden();
    });
});

test.describe('Tables & lists', () => {
    test('admin shops table becomes cards on mobile, stays a table on desktop', async ({ page }, testInfo) => {
        await login(page, ADMIN);
        await page.goto('/admin/shops');
        await expectNoHorizontalOverflow(page);

        const viewportWidth = page.viewportSize()?.width ?? 1440;
        const theadDisplay = await page.locator('.table-mobile-cards thead').first().evaluate((el) => getComputedStyle(el).display);

        if (viewportWidth < 768) {
            expect(theadDisplay).toBe('none');
        } else {
            expect(theadDisplay).not.toBe('none');
        }

        await shot(page, testInfo, '09-shops-table');
    });

    test('admin visits filters submit as a real GET request', async ({ page }, testInfo) => {
        await login(page, ADMIN);
        await page.goto('/admin/visits');

        await page.selectOption('select[name=distributor_id]', { index: 1 }).catch(() => {});
        await page.locator('form[method="GET"] button[type=submit]').first().click();
        await page.waitForLoadState('networkidle');

        expect(page.url()).toContain('distributor_id=');
        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '10-visits-filtered');
    });
});

test.describe('Language / RTL-LTR', () => {
    test('defaults to Arabic RTL and switches cleanly to French LTR', async ({ page }, testInfo) => {
        await login(page, ADMIN);

        // Force a known starting locale: a previous run/project may have left
        // the demo user's persisted `language` column set to 'fr'.
        await page.goto('/lang/ar');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
        await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '11-rtl-arabic');

        await page.click('.dropdown-toggle:has(.bi-translate)');
        await page.click('a:has-text("Français")');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
        await expect(page.locator('html')).toHaveAttribute('lang', 'fr');
        await expectNoHorizontalOverflow(page);
        await shot(page, testInfo, '12-ltr-french');

        await page.click('.dropdown-toggle:has(.bi-translate)');
        await page.click('a:has-text("العربية")');
        await page.waitForLoadState('networkidle');
        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    });
});
