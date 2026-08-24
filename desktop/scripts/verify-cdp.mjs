#!/usr/bin/env node
// Verifikasi otomatis aplikasi desktop via Chrome DevTools Protocol (CDP).
//
// Jalankan aplikasi dengan remote debugging:
//   WEBVIEW2_ADDITIONAL_BROWSER_ARGUMENTS=--remote-debugging-port=9223 ./src-tauri/target/debug/dlh-admin-desktop.exe
// Lalu:
//   node scripts/verify-cdp.mjs [port] [--expect <substring-url>] [--timeout <detik>] [--nav <url>]
//
// Contoh:
//   node scripts/verify-cdp.mjs 9223 --expect silingkardlhpalu.web.id --timeout 40
//   node scripts/verify-cdp.mjs 9223 --expect error.html --timeout 20

const args = process.argv.slice(2);
let port = '9223';
if (/^\d+$/.test(args[0] ?? '')) port = args.shift();

function opt(name) {
  const i = args.indexOf(name);
  return i >= 0 ? args[i + 1] : undefined;
}
const expect = opt('--expect');
const navUrl = opt('--nav');
const timeoutSec = Number(opt('--timeout') ?? 30);

const base = `http://127.0.0.1:${port}`;

async function listTargets() {
  const res = await fetch(`${base}/json/list`);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}

function pageTargets(targets) {
  return targets.filter(t => t.type === 'page');
}

function summarize(targets) {
  return targets.map(t => `  - url=${t.url}\n    title=${JSON.stringify(t.title)}`).join('\n');
}

async function waitFor(expectSubstr, timeoutMs) {
  const start = Date.now();
  let last = '';
  while (Date.now() - start < timeoutMs) {
    try {
      const targets = pageTargets(await listTargets());
      last = summarize(targets);
      for (const t of targets) {
        if (!expectSubstr || t.url.includes(expectSubstr)) {
          console.log(`OK: target ditemukan (${new Date().toISOString()})\n${last}`);
          if (t.webSocketDebuggerUrl) return t;
          return null;
        }
      }
    } catch {
      /* app belum siap — ulangi */
    }
    await new Promise(r => setTimeout(r, 700));
  }
  console.error(`GAGAL: timeout ${timeoutMs} ms menunggu url memuat "${expectSubstr ?? '(apa pun)'}". Target terakhir:\n${last}`);
  process.exit(1);
}

async function navigateViaCdp(target, url) {
  // WebSocket bawaan Node >= 22.
  const ws = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });
  ws.send(JSON.stringify({ id: 1, method: 'Page.navigate', params: { url } }));
  await new Promise(r => setTimeout(r, 1500));
  ws.close();
}

const main = async () => {
  const target = await waitFor(expect, timeoutSec * 1000);
  if (navUrl && target) {
    console.log(`Navigasi via CDP ke: ${navUrl}`);
    await navigateViaCdp(target, navUrl);
    await new Promise(r => setTimeout(r, 2500));
    console.log('Target setelah navigasi:');
    console.log(summarize(pageTargets(await listTargets())));
  }
};

main().catch(e => { console.error('ERROR:', e.message); process.exit(1); });
