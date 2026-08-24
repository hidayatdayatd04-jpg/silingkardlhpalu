// Uji splash per-pindahan halaman: navigasi lalu poll state tiap 250 ms via CDP.
// Pemakaian: node cdp-nav-test.mjs "<url-tujuan>" [durasi_ms]
// Hanya untuk debugging/pengujian — tidak dipakai aplikasi.

const list = await fetch('http://127.0.0.1:9223/json/list').then((r) => r.json());
const page = list.find((p) => p.type === 'page' && p.url.startsWith('http'));
if (!page) { console.error('tidak ada halaman http di CDP'); process.exit(1); }
const ws = new WebSocket(page.webSocketDebuggerUrl);
const target = process.argv[2];
const durationMs = Number(process.argv[3] ?? 12000);
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
await send('Runtime.evaluate', {
  expression: `location.href = ${JSON.stringify(target)}; 'ok'`,
  returnByValue: true,
});
const start = Date.now();
let last = '';
let visibleCount = 0;
while (Date.now() - start < durationMs) {
  await new Promise((r) => setTimeout(r, 250));
  const res = await send('Runtime.evaluate', {
    expression: `JSON.stringify({splash:!!document.getElementById('silingkar-splash'),shell:!!document.querySelector('#silingkar-splash[data-shell]'),ready:document.readyState,path:location.pathname})`,
    returnByValue: true,
  }).catch(() => null);
  const raw = res?.result?.result?.value;
  if (!raw) continue;
  const o = JSON.parse(raw);
  const line = `+${String(Math.round(Date.now() - start)).padStart(5)}ms path=${o.path} ready=${o.ready} splash=${o.splash} shell=${o.shell}`;
  if (o.splash) visibleCount++;
  if (line !== last) { console.log(line); last = line; }
}
console.log(visibleCount > 0
  ? `>>> SPLASH TERLIHAT pada ${visibleCount} sampel`
  : '>>> splash TIDAK tertangkap pada sampel manapun');
process.exit(0);
