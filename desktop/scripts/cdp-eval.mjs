// Evaluasi JavaScript di halaman WebView2 aplikasi desktop via CDP (port 9223).
// Pemakaian: node cdp-eval.mjs "<expression>" [timeout_ms]
// Hanya untuk debugging/pengujian — tidak dipakai aplikasi.

const list = await fetch('http://127.0.0.1:9223/json/list').then((r) => r.json());
const page = list.find((p) => p.type === 'page' && p.url.startsWith('http'));
if (!page) {
  console.error('tidak ada halaman http di CDP');
  process.exit(1);
}
const ws = new WebSocket(page.webSocketDebuggerUrl);
const expr = process.argv[2] ?? 'location.href';
const timeoutMs = Number(process.argv[3] ?? 8000);
ws.onopen = () => {
  ws.send(JSON.stringify({
    id: 1,
    method: 'Runtime.evaluate',
    params: { expression: expr, returnByValue: true, awaitPromise: true },
  }));
};
ws.onmessage = (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id === 1) {
    if (msg.result?.exceptionDetails) {
      console.error('EXCEPTION: ' + JSON.stringify(
        msg.result.exceptionDetails.exception?.description ?? msg.result.exceptionDetails.text));
    } else {
      console.log(JSON.stringify(msg.result.result?.value));
    }
    process.exit(0);
  }
};
ws.onerror = () => { console.error('kesalahan WebSocket'); process.exit(1); };
setTimeout(() => { console.error('timeout CDP'); process.exit(2); }, timeoutMs);
