#!/usr/bin/env node
// ============================================================
//  Puncakmedia Core PMS - Dev Launcher
//  Usage: npm run dev
//  Pre-checks environment lalu jalankan Laravel + Vite sekaligus
// ============================================================

import { execSync, spawn } from "child_process";
import { existsSync, readFileSync } from "fs";
import { resolve } from "path";

const ROOT = resolve(import.meta.dirname, "..");

// -- Helpers --------------------------------------------------
const colors = {
  reset: "\x1b[0m",
  red: "\x1b[31m",
  green: "\x1b[32m",
  yellow: "\x1b[33m",
  cyan: "\x1b[36m",
  magenta: "\x1b[35m",
  dim: "\x1b[2m",
  bold: "\x1b[1m",
  bgBlue: "\x1b[44m",
  white: "\x1b[37m",
};

const c = (color, text) => `${colors[color]}${text}${colors.reset}`;

function ok(msg) {
  console.log(`  ${c("green", "[  OK ]")} ${msg}`);
}
function fail(msg) {
  console.log(`  ${c("red", "[FAIL ]")} ${msg}`);
}
function warn(msg) {
  console.log(`  ${c("yellow", "[ WARN]")} ${msg}`);
}
function info(msg) {
  console.log(`  ${c("magenta", "[INFO ]")} ${msg}`);
}
function header(msg) {
  console.log(`\n  ${c("bgBlue", c("white", ` ${msg} `))}\n`);
}

function run(cmd) {
  return execSync(cmd, { cwd: ROOT, encoding: "utf-8", timeout: 30000, stdio: ["pipe", "pipe", "pipe"] }).trim();
}

function tryRun(cmd) {
  try {
    return { ok: true, output: run(cmd) };
  } catch (e) {
    return { ok: false, output: e.stderr || e.stdout || e.message };
  }
}

const errors = [];
const warnings = [];

// -- Banner ---------------------------------------------------
console.log("");
console.log(c("cyan", "  ========================================"));
console.log(c("cyan", "   Puncakmedia Core PMS - Dev Launcher"));
console.log(c("cyan", "   Laravel 12 + Vite + TailAdmin"));
console.log(c("cyan", "  ========================================"));
console.log("");

// =============================================================
//  PHASE 1 - Tool Check
// =============================================================
header("Phase 1: Checking Required Tools");

// PHP
try {
  const phpV = run("php -v").split("\n")[0];
  const match = phpV.match(/PHP (\d+)\.(\d+)/);
  if (match && (parseInt(match[1]) > 8 || (parseInt(match[1]) === 8 && parseInt(match[2]) >= 2))) {
    ok(`PHP ${match[1]}.${match[2]}`);
  } else {
    fail(`PHP ditemukan tapi butuh >= 8.2`);
    errors.push("PHP version terlalu rendah, butuh >= 8.2");
  }
} catch {
  fail("PHP tidak ditemukan di PATH");
  errors.push("PHP tidak tersedia");
}

// Node
try {
  const nodeV = run("node -v");
  ok(`Node ${nodeV}`);
} catch {
  fail("Node.js tidak ditemukan");
  errors.push("Node.js tidak tersedia");
}

// Composer
try {
  const compV = run("composer --version");
  const m = compV.match(/Composer version (\S+)/);
  ok(`Composer ${m ? m[1] : "OK"}`);
} catch {
  fail("Composer tidak ditemukan");
  errors.push("Composer tidak tersedia");
}

// =============================================================
//  PHASE 2 - Project Files
// =============================================================
header("Phase 2: Checking Project Files");

// .env
if (existsSync(resolve(ROOT, ".env"))) {
  ok(".env ditemukan");

  // APP_KEY
  const envContent = readFileSync(resolve(ROOT, ".env"), "utf-8");
  const keyLine = envContent.split("\n").find((l) => l.startsWith("APP_KEY="));
  if (keyLine && keyLine.length > 10) {
    ok("APP_KEY sudah di-set");
  } else {
    warn("APP_KEY kosong - jalankan: php artisan key:generate");
    warnings.push("APP_KEY belum di-set");
  }
} else {
  warn(".env tidak ada - jalankan: copy .env.example .env");
  warnings.push(".env belum dibuat");
}

// vendor
if (existsSync(resolve(ROOT, "vendor/autoload.php"))) {
  ok("vendor/ terinstall");
} else {
  fail("vendor/ belum ada - jalankan: composer install");
  errors.push("Composer dependencies belum terinstall");
}

// node_modules
if (existsSync(resolve(ROOT, "node_modules"))) {
  ok("node_modules/ terinstall");
} else {
  fail("node_modules/ belum ada - jalankan: npm install");
  errors.push("npm dependencies belum terinstall");
}

// =============================================================
//  PHASE 3 - Database
// =============================================================
header("Phase 3: Checking Database");

const dbResult = tryRun("php artisan migrate:status");
if (dbResult.ok) {
  if (dbResult.output.includes("Pending")) {
    warn("Ada migration yang belum dijalankan");
    warnings.push("Ada pending migrations");
  } else {
    ok("Database & migrations OK");
  }
} else {
  warn("Database belum bisa dicek (pastikan MySQL aktif)");
  warnings.push("Koneksi database gagal dicek");
}

// =============================================================
//  PHASE 4 - Smoke Test
// =============================================================
header("Phase 4: Quick Smoke Test");

// Routes
const routeResult = tryRun("php artisan route:list --json");
if (routeResult.ok) {
  try {
    const routes = JSON.parse(routeResult.output);
    ok(`${routes.length} routes terdaftar`);
  } catch {
    ok("Routes bisa di-load");
  }
} else {
  warn("Route list gagal - mungkin ada syntax error");
  warnings.push("Route list gagal");
}

// Views
const viewResult = tryRun("php artisan view:cache");
if (viewResult.ok) {
  ok("Blade views OK");
  tryRun("php artisan view:clear");
} else {
  warn("Blade views ada masalah");
  warnings.push("View cache gagal");
}

// =============================================================
//  PHASE 5 - Feature Tests
// =============================================================
header("Phase 5: Running Tests");

const testResult = tryRun("php artisan test");
if (testResult.ok) {
  const lines = testResult.output.split("\n").filter((l) => l.trim());
  const summary = lines.slice(-3);
  summary.forEach((l) => info(l.trim()));
  ok("Semua test PASSED");
} else {
  const lines = (testResult.output || "").split("\n").filter((l) => l.trim());
  const summary = lines.slice(-5);
  summary.forEach((l) => info(l.trim()));
  warn("Beberapa test gagal (server tetap bisa jalan)");
  warnings.push("Ada test yang gagal");
}

// =============================================================
//  SUMMARY
// =============================================================
console.log("");
console.log(c("cyan", "  ----------------------------------------"));
console.log(c("bold", "            CHECK RESULT SUMMARY"));
console.log(c("cyan", "  ----------------------------------------"));

if (warnings.length > 0) {
  console.log(`\n  ${c("yellow", `Warnings (${warnings.length}):`)} `);
  warnings.forEach((w) => console.log(`    ${c("yellow", "!")} ${w}`));
}

if (errors.length > 0) {
  console.log(`\n  ${c("red", `Errors (${errors.length}):`)} `);
  errors.forEach((e) => console.log(`    ${c("red", "x")} ${e}`));
  console.log(`\n  ${c("red", "x Ada error kritikal. Perbaiki dulu sebelum menjalankan server.")}`);
  process.exit(1);
}

if (warnings.length === 0) {
  console.log(`\n  ${c("green", "Semua check PASSED!")}`);
}

// =============================================================
//  PHASE 6 - Launch
// =============================================================
console.log("");
header("Launching Dev Servers");
console.log(`  ${c("cyan", "Laravel")}  ->  http://127.0.0.1:8000`);
console.log(`  ${c("magenta", "Vite")}     ->  http://127.0.0.1:5173 (HMR)`);
console.log(`\n  ${c("dim", "Tekan Ctrl+C untuk stop semua")}\n`);

// Spawn Laravel
const laravel = spawn("php", ["artisan", "serve"], {
  cwd: ROOT,
  stdio: ["ignore", "pipe", "pipe"],
  shell: true,
});

// Spawn Vite
const vite = spawn("npx", ["vite"], {
  cwd: ROOT,
  stdio: ["ignore", "pipe", "pipe"],
  shell: true,
});

// Prefix output
laravel.stdout.on("data", (d) => {
  d.toString().split("\n").filter(Boolean).forEach((line) => {
    console.log(`  ${c("cyan", "[laravel]")} ${line}`);
  });
});
laravel.stderr.on("data", (d) => {
  d.toString().split("\n").filter(Boolean).forEach((line) => {
    console.log(`  ${c("cyan", "[laravel]")} ${line}`);
  });
});

vite.stdout.on("data", (d) => {
  d.toString().split("\n").filter(Boolean).forEach((line) => {
    console.log(`  ${c("magenta", "[vite]")}    ${line}`);
  });
});
vite.stderr.on("data", (d) => {
  d.toString().split("\n").filter(Boolean).forEach((line) => {
    console.log(`  ${c("magenta", "[vite]")}    ${line}`);
  });
});

// Handle exit
function cleanup() {
  console.log(`\n  ${c("yellow", "Stopping servers...")}`);
  laravel.kill();
  vite.kill();
  process.exit(0);
}

process.on("SIGINT", cleanup);
process.on("SIGTERM", cleanup);

laravel.on("close", (code) => {
  if (code !== null && code !== 0) {
    console.log(`  ${c("red", "[laravel] crashed with code " + code)}`);
  }
  vite.kill();
  process.exit(code || 0);
});

vite.on("close", (code) => {
  if (code !== null && code !== 0) {
    console.log(`  ${c("red", "[vite] crashed with code " + code)}`);
  }
  laravel.kill();
  process.exit(code || 0);
});
