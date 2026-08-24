// Wrapper perintah Tauri (`npm run dev` / `npm run build`) agar cargo SELALU
// dikenali, apa pun kondisi PATH terminal — pengguna tidak perlu mengatur
// PATH manual. Bila cargo belum terdaftar di PATH, lokasi standar rustup
// (%USERPROFILE%\.cargo\bin) ditambahkan otomatis ke proses ini saja.
//
// Bila Rust memang belum terpasang, tampilkan panduan install yang jelas.

import { existsSync } from "node:fs";
import { spawnSync } from "node:child_process";
import os from "node:os";
import path from "node:path";

const task = process.argv[2] || "build";
const isWin = process.platform === "win32";
const sep = isWin ? ";" : ":";
const cargoExe = isWin ? "cargo.exe" : "cargo";

const cargoDirs = [
  path.join(os.homedir(), ".cargo", "bin"), // lokasi default rustup
  path.join(process.env.CARGO_HOME || "", "bin"), // rustup dengan CARGO_HOME kustom
].filter(Boolean);

const pathEntries = (process.env.PATH || "").split(sep).filter(Boolean);
const inPath = pathEntries.some((dir) => existsSync(path.join(dir, cargoExe)));
const onDisk = cargoDirs.find((dir) => existsSync(path.join(dir, cargoExe)));

if (!inPath && !onDisk) {
  console.error(
    "\n[rust] Rust (cargo) belum terpasang di komputer ini.\n" +
      "       1. Buka https://rustup.rs\n" +
      "       2. Download & jalankan rustup-init.exe (pilihan default saja)\n" +
      "       3. Tutup lalu buka ulang terminal, dan jalankan perintah ini lagi\n"
  );
  process.exit(1);
}

const env = { ...process.env };
if (!inPath && onDisk) {
  env.PATH = [...pathEntries, onDisk].join(sep);
  console.log(`[rust] cargo otomatis ditambahkan dari: ${onDisk}`);
}

// npx menjalankan CLI Tauri lokal (node_modules) tanpa menyentuh jaringan.
const result = spawnSync("npx", ["tauri", task], {
  stdio: "inherit",
  shell: isWin,
  env,
});

process.exit(result.status ?? 1);
