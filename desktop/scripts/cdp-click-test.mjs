// Uji latensi splash saat klik link internal: klik menu, ukur seberapa
// seketika splash tampil (harus <=~200 ms, BUKAN menunggu TTFB server).
// Pemakaian: node cdp-click-test.mjs [jumlah_klik=2]
// Hanya untuk debugging/pengujian — tidak dipakai aplikasi.

const list = await fetch('http://127.0.0.1:9223/json/list').then((r) => r.json());
const page = list.find((p) => p.type === 'page' && p.url.startsWith('http'));
if (!page) { console.error('tidak ada halaman http di CDP'); process.exit(1); }
const ws = new WebSocket(page.webSocketDebuggerUrl);
let id = 0;
const pending = new Map();
function send(method, params) {
  return new Promise((resolve) => {
    const mid = ++id;
    pending.set(mid, resolve);
    ws.send(JSON.stringify({ id: mid, method, params }));
  });
}
ws.onmessage = (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg); pending.delete(msg.id); }
};
await new Promise((res) => { ws.onopen = res; });

async function evalJson(expression) {
  const res = await send('Runtime.evaluate', {
    expression,
    returnByValue: true,
    awaitPromise: true,
  }).catch(() => null);
  const raw = res?.result?.result?.value;
  try { return JSON.parse(raw); } catch { return null; }
}

const clicks = Number(process.argv[2] ?? 2);
let pass = 0;
for (let n = 1; n <= clicks; n++) {
  // 1) Tunggu halaman siap dan splash awal sudah terangkat.
  let ready = false;
  for (let t = 0; t < 120; t++) {
    const o = await evalJson(
      `JSON.stringify((() => { const sp = document.getElementById('silingkar-splash');
        return { ready: document.readyState, path: location.pathname,
          splashGone: !sp || sp.classList.contains('sk-pre--hide') }; })())`,
    );
    if (o?.ready === 'complete' && o.splashGone) { ready = true; break; }
    await new Promise((r) => setTimeout(r, 250));
  }
  if (!ready) { console.log(`klik #${n}: halaman belum siap — lewati`); continue; }

  // 2) Klik link internal pertama (utamakan menu/sidebar), ukur latensi splash.
  const r = await evalJson(`(async () => {
    const all = [...document.querySelectorAll('a[href]')].filter((a) => {
      if ((a.getAttribute('target') || '').toLowerCase() === '_blank') return false;
      if (a.hasAttribute('download')) return false;
      let u; try { u = new URL(a.href, location.href); } catch { return false; }
      if (u.protocol !== 'http:' && u.protocol !== 'https:') return false;
      const prod = ['www.silingkardlhpalu.web.id', 'silingkardlhpalu.web.id', location.host.toLowerCase()];
      if (!prod.includes(u.host.toLowerCase())) return false;
      if (u.pathname + u.search === location.pathname + location.search) return false;
      const b = a.getBoundingClientRect();
      return b.width > 0 && b.height > 0;
    });
    const menu = all.filter((a) => a.closest('aside, nav'));
    const a = menu[0] || all[0];
    if (!a) return JSON.stringify({ error: 'tidak ada link internal terlihat' });
    const dest = a.pathname + a.search;
    const t0 = performance.now();
    a.click();
    let visibleAt = null;
    for (let i = 0; i < 400; i++) {
      const sp = document.getElementById('silingkar-splash');
      if (sp && !sp.classList.contains('sk-pre--hide')) {
        visibleAt = Math.round(performance.now() - t0);
        break;
      }
      await new Promise((r2) => setTimeout(r2, 5));
    }
    return JSON.stringify({
      dest,
      visibleAt,
      shellSplash: !!document.querySelector('#silingkar-splash[data-shell]'),
    });
  })()`);
  if (!r || r.error) { console.log(`klik #${n}: ${r?.error ?? 'eval gagal'}`); continue; }
  const verdict = r.visibleAt == null
    ? 'GAGAL — splash tidak tampil'
    : r.visibleAt <= 200 ? 'OK' : 'LAMBAT';
  console.log(`klik #${n} → ${r.dest} | splash tampil setelah ${r.visibleAt ?? '?'} ms [${verdict}] | splash shell=${r.shellSplash}`);
  if (r.visibleAt != null && r.visibleAt <= 200) pass++;

  // 3) Tunggu halaman tujuan termuat dan splash terangkat kembali. Catat
  // kondisi splash halaman baru saat pertama terlihat: harus TUNGGAL dan
  // ber-mode lanjut (resume) — bukan dua splash / intro diulang dari awal.
  let landed = null;
  let firstSeen = null;
  for (let t = 0; t < 240; t++) {
    const o = await evalJson(
      `JSON.stringify((() => { const sps = document.querySelectorAll('#silingkar-splash');
        const sp = sps[0];
        return { ready: document.readyState, path: location.pathname, n: sps.length,
          resume: sp ? sp.classList.contains('sk-pre--resume') : null,
          shell: sp ? sp.hasAttribute('data-shell') : null,
          splashGone: !sp || sp.classList.contains('sk-pre--hide'),
          t: Math.round(performance.now()) }; })())`,
    );
    if (o && !firstSeen && o.n > 0 && !o.splashGone) firstSeen = o;
    if (o?.ready === 'complete' && o.splashGone) { landed = o; break; }
    await new Promise((r3) => setTimeout(r3, 100));
  }
  if (landed) {
    const info = firstSeen
      ? `splash baru: jumlah=${firstSeen.n} resume=${firstSeen.resume} shell=${firstSeen.shell} (terlihat t=${firstSeen.t} ms)`
      : 'splash baru TIDAK terdeteksi';
    const ok = firstSeen && firstSeen.n === 1 && firstSeen.resume === true;
    console.log(`klik #${n}: halaman tujuan termuat (${landed.path}), splash terangkat | ${info} [${ok ? 'SATU & LANJUT' : 'PERIKSA'}]`);
  } else {
    console.log(`klik #${n}: halaman tujuan tidak terkonfirmasi termuat`);
  }
}
console.log(pass === clicks && clicks > 0
  ? `>>> LULUS: ${pass}/${clicks} klik memunculkan splash seketika (<=200 ms)`
  : `>>> TIDAK LULUS: ${pass}/${clicks} klik`);
process.exit(pass === clicks && clicks > 0 ? 0 : 1);
