import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { defineConfig, devices } from '@playwright/test';

function envTestingValue(key: string): string | undefined {
  const file = join(process.cwd(), '.env.testing');
  if (!existsSync(file)) return undefined;
  const line = readFileSync(file, 'utf8')
    .split(/\r?\n/)
    .find((entry) => entry.trim().startsWith(`${key}=`));

  return line?.split('=').slice(1).join('=').trim().replace(/^["']|["']$/g, '');
}

const baseURL = process.env.BASE_URL || envTestingValue('BASE_URL') || envTestingValue('APP_URL') || 'http://localhost:8000';

export default defineConfig({
  testDir: './',
  testMatch: ['admin/**/*.spec.ts', 'public/**/*.spec.ts', 'auth/**/*.spec.ts', 'e2e/**/*.spec.ts'],
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [
    ['list'],
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
  ],
  timeout: 60_000,
  expect: { timeout: 10_000 },

  use: {
    baseURL,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15_000,
    navigationTimeout: 30_000,
    locale: 'id-ID',
    timezoneId: 'Asia/Makassar',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  outputDir: 'test-results',
});
