import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import type { Page } from '@playwright/test';
import type { AdminField, ResourceDef } from '../admin/resources';

const fixtureDir = join(process.cwd(), 'tests', 'playwright', '.fixtures');
export const pdfFixture = join(fixtureDir, 'document.pdf');
export const imageFixture = join(fixtureDir, 'image.png');

export function ensureUploadFixtures(): void {
  mkdirSync(fixtureDir, { recursive: true });
  writeFileSync(pdfFixture, '%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n');
  writeFileSync(
    imageFixture,
    Buffer.from(
      'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/lw0H4wAAAABJRU5ErkJggg==',
      'base64',
    ),
  );
}

function selectValue(field: AdminField): string {
  const keys = Object.keys(field.options ?? {}).filter((key) => key !== '');
  return keys[0] ?? '';
}

export function valueForField(field: AdminField, resource: ResourceDef): string {
  const stamp = Date.now().toString().slice(-6);
  const name = field.name;

  if (field.type === 'select') return selectValue(field);
  if (field.type === 'email') return `e2e-${resource.slug}-${stamp}@example.test`;
  if (field.type === 'tel' || name.includes('telepon') || name.includes('hp')) return '081234567890';
  if (field.type === 'date' || name.includes('tanggal')) return '2026-08-15';
  if (name.includes('jam_mulai')) return '08:00';
  if (name.includes('jam_selesai')) return '16:00';
  if (field.type === 'number' || ['tahun', 'jumlah', 'luas', 'volume_sampah', 'diameter', 'panjang', 'skor'].includes(name)) return '12';
  if (name.includes('latitude')) return '-0.901234';
  if (name.includes('longitude')) return '119.872345';
  if (field.type === 'password') return 'Password123!';
  if (field.type === 'textarea' || name.includes('alamat') || name.includes('deskripsi') || name.includes('catatan')) {
    return `E2E ${field.label} ${stamp}`;
  }
  if (field.type === 'jodit') {
    return `<p>E2E ${field.label} ${stamp}</p>`;
  }

  return `E2E ${field.label} ${stamp}`;
}

async function fillCustomSelect(page: Page, fieldName: string, optionValue: string): Promise<boolean> {
  // Detect custom x-admin.select: hidden input + sibling button toggle
  const hiddenInput = page.locator(`input[type="hidden"][name="${fieldName}"]`).first();
  if ((await hiddenInput.count()) === 0) return false;

  // Check if this is a custom Alpine select (has x-ref="hiddenInput" nearby)
  const isCustom = await hiddenInput.evaluate((el) => {
    return el.hasAttribute('x-ref') || el.closest('[x-data]') !== null;
  }).catch(() => false);

  if (!isCustom) return false;

  // Use Alpine.js to set the value directly
  await page.evaluate(({ name, value }) => {
    // Find the Alpine component data
    const wrapper = document.querySelector(`input[type="hidden"][name="${name}"]`)?.closest('[x-data]');
    if (wrapper && (wrapper as any)._x_dataStack) {
      const data = (wrapper as any)._x_dataStack[0];
      if (data && typeof data.selectOption === 'function') {
        const option = data.options?.find((o: any) => o.value === value);
        if (option) {
          data.selectOption(option.value, option.label);
          return;
        }
      }
    }
    // Fallback: set hidden input value directly
    const input = document.querySelector(`input[type="hidden"][name="${name}"]`) as HTMLInputElement;
    if (input) {
      input.value = value;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }, { name: fieldName, value: optionValue });

  return true;
}

export async function fillAdminForm(page: Page, resource: ResourceDef): Promise<Record<string, string>> {
  ensureUploadFixtures();
  const filled: Record<string, string> = {};

  for (const field of resource.fields) {
    if (field.type === 'section' || field.readonly || field.hide_on_create) continue;
    const locator = page.locator(`[name="${field.name}"], [name="${field.name}[]"]`).first();
    if ((await locator.count()) === 0) continue;

    if (field.type === 'checkbox') {
      await locator.check({ force: true }).catch(() => undefined);
      filled[field.name] = '1';
      continue;
    }

    if (['file', 'photos', 'relation_files'].includes(field.type)) {
      const acceptsImage = (field.accept ?? '').includes('image') || (field.accept ?? '').includes('jpg') || field.type === 'photos';
      await locator.setInputFiles(acceptsImage ? imageFixture : pdfFixture);
      filled[field.name] = acceptsImage ? imageFixture : pdfFixture;
      continue;
    }

    const value = valueForField(field, resource);
    if (!value && field.type === 'select') continue;

    if (field.type === 'select') {
      // Try custom Alpine select first, fallback to native selectOption
      const handled = await fillCustomSelect(page, field.name, value);
      if (!handled) {
        await locator.selectOption(value).catch(async () => {
          // If selectOption fails, try setting value via Alpine
          await fillCustomSelect(page, field.name, value);
        });
      }
    } else if (field.type === 'jodit') {
      // Jodit editor: textarea is hidden, fill via JavaScript
      await page.evaluate(({ name, val }) => {
        const textarea = document.querySelector(`textarea[name="${name}"]`) as HTMLTextAreaElement;
        if (textarea) textarea.value = val;
        // Also try setting via Jodit instance
        const editors = (window as any).__joditEditors;
        if (editors && editors.length > 0) {
          const editor = editors[editors.length - 1];
          if (editor && typeof editor.value !== 'undefined') {
            editor.value = val;
          }
        }
      }, { name: field.name, val: value });
      filled[field.name] = value;
      continue;
    } else {
      await locator.fill(value);
    }
    filled[field.name] = value;
  }

  return filled;
}

export async function submitAndWait(page: Page, urlPattern?: RegExp): Promise<void> {
  await Promise.all([
    page.waitForLoadState('networkidle').catch(() => undefined),
    page.locator('button[type="submit"]').last().click(),
  ]);
  if (urlPattern) {
    // Wait up to 20s for URL change (Livewire may redirect after AJAX)
    await page.waitForURL(urlPattern, { timeout: 20_000 }).catch(() => undefined);
    // If URL still doesn't match, give Livewire a bit more time then continue
    if (!urlPattern.test(page.url())) {
      await page.waitForTimeout(3_000);
    }
  }
}

