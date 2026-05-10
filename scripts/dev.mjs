#!/usr/bin/env node
// ============================================================
//  Puncakmedia Core PMS - Dev Launcher
//  Usage: npm run dev
//  Pre-check environment lalu jalankan Laravel + Vite
// ============================================================

import { execFileSync, spawn } from "child_process";
import { existsSync, readFileSync } from "fs";
import { dirname, resolve } from "path";

const ROOT = resolve(import.meta.dirname, "..");
const IS_WINDOWS = process.platform === "win32";
const VITE_BIN = resolve(ROOT, "node_modules", "vite", "bin", "vite.js");
const SHOULD_RUN_TESTS = String(process.env.DEV_RUN_TESTS || "").toLowerCase() === "true";
const SHOULD_START_LARAGON = !["false", "0", "no"].includes(String(process.env.DEV_START_LARAGON || "true").toLowerCase());
const LARAGON_EXE = "C:\\laragon\\laragon.exe";
const LARAGON_INI = "C:\\laragon\\usr\\laragon.ini";

const colors = {
  reset: "\x1b[0m",
  red: "\x1b[31m",
  green: "\x1b[32m",
  yellow: "\x1b[33m",
  blue: "\x1b[34m",
  magenta: "\x1b[35m",
  cyan: "\x1b[36m",
  purple: "\x1b[38;5;141m",
  orange: "\x1b[38;5;214m",
  gray: "\x1b[90m",
  white: "\x1b[37m",
  bold: "\x1b[1m",
  bgBlue: "\x1b[44m",
  bgMagenta: "\x1b[45m",
  bgCyan: "\x1b[46m",
};

const c = (color, text) => `${colors[color] ?? ""}${text}${colors.reset}`;

const icons = {
  ok: "✔",
  fail: "✖",
  warn: "▲",
  info: "●",
  php: "🐘",
  vite: "⚡",
  laravel: "🌿",
  tools: "🧰",
  files: "📦",
  database: "🗄",
  tests: "🧪",
  launch: "🚀",
};

const errors = [];
const warnings = [];
const executableCache = new Map();

function line() {
  console.log(c("gray", "  ───────────────────────────────────────────────"));
}

function header(icon, title, accent = "bgBlue") {
  console.log("");
  console.log(`  ${c(accent, c("white", ` ${icon} ${title} `))}`);
  console.log("");
}

function ok(message) {
  console.log(`  ${c("green", icons.ok)} ${message}`);
}

function fail(message) {
  console.log(`  ${c("red", icons.fail)} ${message}`);
}

function warn(message) {
  console.log(`  ${c("yellow", icons.warn)} ${message}`);
}

function info(message) {
  console.log(`  ${c("cyan", icons.info)} ${message}`);
}

function dim(text) {
  return c("gray", text);
}

function timestamp() {
  return new Date().toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
}

function fullDateTime() {
  const now = new Date();

  const day = now.toLocaleDateString("id-ID", { weekday: "long" });
  const date = now.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
  const time = now.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });

  return {
    day,
    date,
    time,
  };
}

function renderBanner() {
  const lines = [
    "██████╗  ██╗   ██╗ ███╗   ██╗  ██████╗  █████╗  ██╗  ██╗ ███╗   ███╗ ███████╗ ██████╗  ██╗  █████╗ ",
    "██╔══██╗ ██║   ██║ ████╗  ██║ ██╔════╝ ██╔══██╗ ██║ ██╔╝ ████╗ ████║ ██╔════╝ ██╔══██╗ ██║ ██╔══██╗",
    "██████╔╝ ██║   ██║ ██╔██╗ ██║ ██║      ███████║ █████╔╝  ██╔████╔██║ █████╗   ██║  ██║ ██║ ███████║",
    "██╔═══╝  ██║   ██║ ██║╚██╗██║ ██║      ██╔══██║ ██╔═██╗  ██║╚██╔╝██║ ██╔══╝   ██║  ██║ ██║ ██╔══██║",
    "██║      ╚██████╔╝ ██║ ╚████║ ╚██████╗ ██║  ██║ ██║  ██╗ ██║ ╚═╝ ██║ ███████╗ ██████╔╝ ██║ ██║  ██║",
    "╚═╝       ╚═════╝  ╚═╝  ╚═══╝  ╚═════╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝ ╚═╝     ╚═╝ ╚══════╝ ╚═════╝  ╚═╝ ╚═╝  ╚═╝",
  ];

  lines.forEach((line, index) => {
    const accent = index % 2 === 0 ? "green" : "orange";
    console.log(c(accent, `  ${line}`));
  });
}

function renderRetroFrame() {
  console.log(c("gray", "  ┌──────────────────────────────────────────────────────────────┐"));
  console.log(c("gray", "  │  CRT LINK STABLE  ::  DEV TERMINAL ONLINE                   │"));
  console.log(c("gray", "  └──────────────────────────────────────────────────────────────┘"));
}

function quoteArg(value) {
  const text = String(value ?? "");

  if (text === "") {
    return '""';
  }

  if (!/[ \t"]/u.test(text)) {
    return text;
  }

  return `"${text.replace(/"/g, '""')}"`;
}

function buildCmdCommand(command, args = []) {
  const inner = [quoteArg(command), ...args.map(quoteArg)].join(" ");
  return `"${inner}"`;
}

function locateExecutable(base) {
  if (executableCache.has(base)) {
    return executableCache.get(base);
  }

  if (!IS_WINDOWS) {
    executableCache.set(base, base);
    return base;
  }

  if (base === "node") {
    executableCache.set(base, process.execPath);
    return process.execPath;
  }

  const candidates = {
    npm: ["npm.cmd", "npm"],
    composer: ["composer.bat", "composer", "composer.cmd"],
    php: ["php.exe", "php"],
  }[base] ?? [base];

  for (const candidate of candidates) {
    try {
      const output = execFileSync("where.exe", [candidate], {
        cwd: ROOT,
        encoding: "utf-8",
        stdio: ["ignore", "pipe", "ignore"],
      })
        .split(/\r?\n/)
        .map((line) => line.trim())
        .find(Boolean);

      if (output) {
        executableCache.set(base, output);
        return output;
      }
    } catch {
      // continue searching
    }
  }

  const fallback = candidates[0];
  executableCache.set(base, fallback);
  return fallback;
}

function run(command, args = []) {
  const resolved = locateExecutable(command);

  if (IS_WINDOWS && /\.(bat|cmd)$/i.test(resolved)) {
    const commandLine = buildCmdCommand(resolved, args);

    return execFileSync("cmd.exe", ["/d", "/s", "/c", commandLine], {
      cwd: ROOT,
      encoding: "utf-8",
      timeout: 30000,
      stdio: ["pipe", "pipe", "pipe"],
    }).trim();
  }

  return execFileSync(resolved, args, {
    cwd: ROOT,
    encoding: "utf-8",
    timeout: 30000,
    stdio: ["pipe", "pipe", "pipe"],
  }).trim();
}

function tryRun(command, args = []) {
  try {
    return { ok: true, output: run(command, args) };
  } catch (error) {
    return {
      ok: false,
      output: error.stderr || error.stdout || error.message,
    };
  }
}

function readEnvValue(key) {
  if (!existsSync(resolve(ROOT, ".env"))) {
    return null;
  }

  const envContent = readFileSync(resolve(ROOT, ".env"), "utf-8");
  const target = envContent
    .split("\n")
    .map((line) => line.trim())
    .find((line) => line.startsWith(`${key}=`));

  if (!target) {
    return null;
  }

  return target.slice(key.length + 1).replace(/^['"]|['"]$/g, "");
}

function sleep(ms) {
  Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);
}

function parseIni(content) {
  const result = {};
  let currentSection = null;

  for (const rawLine of content.split(/\r?\n/)) {
    const line = rawLine.trim();

    if (!line || line.startsWith(";") || line.startsWith("#")) {
      continue;
    }

    const sectionMatch = line.match(/^\[(.+)\]$/);
    if (sectionMatch) {
      currentSection = sectionMatch[1];
      result[currentSection] = result[currentSection] ?? {};
      continue;
    }

    const separatorIndex = line.indexOf("=");
    if (separatorIndex === -1 || !currentSection) {
      continue;
    }

    const key = line.slice(0, separatorIndex).trim();
    const value = line.slice(separatorIndex + 1).trim();
    result[currentSection][key] = value;
  }

  return result;
}

function readLaragonConfig() {
  if (!IS_WINDOWS || !existsSync(LARAGON_INI)) {
    return null;
  }

  try {
    return parseIni(readFileSync(LARAGON_INI, "utf-8"));
  } catch {
    return null;
  }
}

function isLaragonRunning() {
  if (!IS_WINDOWS) {
    return false;
  }

  try {
    const output = execFileSync("tasklist.exe", ["/FI", "IMAGENAME eq laragon.exe"], {
      cwd: ROOT,
      encoding: "utf-8",
      stdio: ["ignore", "pipe", "ignore"],
    });

    return /laragon\.exe/i.test(output);
  } catch {
    return false;
  }
}

function isProcessRunning(imageName) {
  if (!IS_WINDOWS) {
    return false;
  }

  try {
    const output = execFileSync("tasklist.exe", ["/FI", `IMAGENAME eq ${imageName}`], {
      cwd: ROOT,
      encoding: "utf-8",
      stdio: ["ignore", "pipe", "ignore"],
    });

    return new RegExp(imageName.replace(".", "\\."), "i").test(output);
  } catch {
    return false;
  }
}

function startDetachedExecutable(executable, args = [], options = {}) {
  const child = spawn(executable, args, {
    detached: true,
    stdio: "ignore",
    windowsHide: true,
    ...options,
  });

  child.unref();
}

function waitForProcess(imageName, attempts = 8, delayMs = 500) {
  for (let attempt = 0; attempt < attempts; attempt += 1) {
    if (isProcessRunning(imageName)) {
      return true;
    }

    sleep(delayMs);
  }

  return false;
}

function startLaragonServices(config) {
  if (!IS_WINDOWS || !config) {
    return [];
  }

  const services = [];

  const mysqlVersion = config.mysql?.Version;
  const mysqlUse = config.mysql?.Use === "-1";
  if (mysqlUse && mysqlVersion) {
    const mysqldPath = `C:\\laragon\\bin\\mysql\\${mysqlVersion}\\bin\\mysqld.exe`;
    const myIniPath = `C:\\laragon\\bin\\mysql\\${mysqlVersion}\\my.ini`;

    if (existsSync(mysqldPath) && existsSync(myIniPath)) {
      if (isProcessRunning("mysqld.exe")) {
        ok("MySQL Laragon sudah berjalan");
        services.push({ name: "MySQL", started: false, running: true });
      } else {
        try {
          startDetachedExecutable(mysqldPath, [`--defaults-file=${myIniPath}`], {
            cwd: dirname(mysqldPath),
          });
          const running = waitForProcess("mysqld.exe");

          if (running) {
            ok("MySQL Laragon berhasil dijalankan");
          } else {
            warn("MySQL Laragon belum terdeteksi aktif setelah startup");
          }

          services.push({ name: "MySQL", started: true, running });
        } catch (error) {
          warn(`MySQL Laragon gagal dijalankan: ${error.message}`);
          services.push({ name: "MySQL", started: false, running: false });
        }
      }
    }
  }

  const apacheVersion = config.apache?.Version;
  const apacheUse = config.apache?.Use === "-1";
  if (apacheUse && apacheVersion) {
    const httpdPath = `C:\\laragon\\bin\\apache\\${apacheVersion}\\bin\\httpd.exe`;

    if (existsSync(httpdPath)) {
      if (isProcessRunning("httpd.exe")) {
        ok("Apache Laragon sudah berjalan");
        services.push({ name: "Apache", started: false, running: true });
      } else {
        try {
          startDetachedExecutable(httpdPath, [], {
            cwd: dirname(httpdPath),
          });
          const running = waitForProcess("httpd.exe");

          if (running) {
            ok("Apache Laragon berhasil dijalankan");
          } else {
            warn("Apache Laragon belum terdeteksi aktif setelah startup");
          }

          services.push({ name: "Apache", started: true, running });
        } catch (error) {
          warn(`Apache Laragon gagal dijalankan: ${error.message}`);
          services.push({ name: "Apache", started: false, running: false });
        }
      }
    }
  }

  return services;
}

function startLaragonIfNeeded() {
  if (!IS_WINDOWS || !SHOULD_START_LARAGON) {
    return { attempted: false, started: false, running: false, path: null, services: [] };
  }

  if (!existsSync(LARAGON_EXE)) {
    warnings.push("Laragon tidak ditemukan di C:\\laragon\\laragon.exe");
    warn("Laragon tidak ditemukan, launcher lanjut tanpa auto-start");
    return { attempted: true, started: false, running: false, path: null, services: [] };
  }

  const config = readLaragonConfig();

  if (isLaragonRunning()) {
    ok("Laragon sudah berjalan");
    return {
      attempted: true,
      started: false,
      running: true,
      path: LARAGON_EXE,
      services: startLaragonServices(config),
    };
  }

  try {
    const laragon = spawn(LARAGON_EXE, [], {
      cwd: dirname(LARAGON_EXE),
      detached: true,
      stdio: "ignore",
      windowsHide: false,
    });

    laragon.unref();
    ok("Laragon berhasil dijalankan otomatis");
    info("Tunggu sebentar jika MySQL / Apache masih proses startup");
    return {
      attempted: true,
      started: true,
      running: true,
      path: LARAGON_EXE,
      services: startLaragonServices(config),
    };
  } catch (error) {
    warnings.push("Laragon gagal dijalankan otomatis");
    warn(`Laragon gagal dijalankan otomatis: ${error.message}`);
    return { attempted: true, started: false, running: false, path: LARAGON_EXE, services: [] };
  }
}

function createProcess(command, args, options = {}) {
  const resolved = locateExecutable(command);

  if (IS_WINDOWS && /\.(bat|cmd)$/i.test(resolved)) {
    const commandLine = buildCmdCommand(resolved, args);

    return spawn("cmd.exe", ["/d", "/s", "/c", commandLine], {
      cwd: ROOT,
      stdio: ["ignore", "pipe", "pipe"],
      shell: false,
      ...options,
    });
  }

  return spawn(resolved, args, {
    cwd: ROOT,
    stdio: ["ignore", "pipe", "pipe"],
    shell: false,
    ...options,
  });
}

function stripAnsi(text) {
  return text.replace(/\x1B\[[0-9;]*m/g, "");
}

function normalizeLine(line) {
  return stripAnsi(String(line ?? ""))
    .replace(/\r/g, "")
    .replace(/\u001bc/g, "")
    .trimEnd();
}

function classifyLaravelLine(line) {
  if (/ERROR|Exception|Failed|ParseError|Fatal/i.test(line)) return "error";
  if (/WARN|warning/i.test(line)) return "warn";
  if (/\bGET\b|\bPOST\b|\bPUT\b|\bPATCH\b|\bDELETE\b/.test(line)) return "request";
  if (/server running|development server/i.test(line)) return "success";
  return "default";
}

function classifyViteLine(line) {
  if (/error/i.test(line)) return "error";
  if (/warning/i.test(line)) return "warn";
  if (/Local:|Network:|ready in/i.test(line)) return "success";
  return "default";
}

function classifyQueueLine(line) {
  if (/error|exception|failed/i.test(line)) return "error";
  if (/warning|warn/i.test(line)) return "warn";
  if (/processing|running|worked|done/i.test(line)) return "success";
  return "default";
}

function colorizeLogLine(line, kind) {
  if (kind === "error") return c("red", line);
  if (kind === "warn") return c("yellow", line);
  if (kind === "success") return c("green", line);
  if (kind === "request") return c("cyan", line);
  return line;
}

function wireLogs(processRef, config) {
  let previousBlank = false;

  const flush = (chunk, streamType) => {
    const lines = chunk
      .toString()
      .split("\n")
      .map(normalizeLine);

    for (const rawLine of lines) {
      const line = rawLine.trim();

      if (!line) {
        if (!previousBlank) {
          previousBlank = true;
        }
        continue;
      }

      previousBlank = false;

      const kind = config.classify(line, streamType);
      const tag = `${config.icon} ${config.label}`.padEnd(11, " ");
      const renderedTag = c(config.color, tag);
      const renderedLine = colorizeLogLine(line, kind);
      console.log(`  ${dim(timestamp())} ${renderedTag} ${renderedLine}`);
    }
  };

  processRef.stdout.on("data", (chunk) => flush(chunk, "stdout"));
  processRef.stderr.on("data", (chunk) => flush(chunk, "stderr"));
}

const launchMoment = fullDateTime();

console.log("");
renderBanner();
renderRetroFrame();
console.log(`  ${c("orange", "✦")} ${c("bold", "Hari")}      : ${c("green", launchMoment.day.toUpperCase())}`);
console.log(`  ${c("orange", "✦")} ${c("bold", "Tanggal")}   : ${c("yellow", launchMoment.date.toUpperCase())}`);
console.log(`  ${c("orange", "✦")} ${c("bold", "Waktu")}     : ${c("magenta", launchMoment.time)}`);
console.log(`  ${c("green", ">")} ${c("bold", "SYSTEM READY")} ${c("gray", "::")} ${c("cyan", "AWAITING SERVICE BOOT")}`);
console.log("");

header(icons.tools, "Phase 1: Checking Required Tools", "bgCyan");

try {
  const phpVersion = run("php", ["-v"]).split("\n")[0];
  const match = phpVersion.match(/PHP (\d+)\.(\d+)/);
  if (match && (Number.parseInt(match[1], 10) > 8 || (Number.parseInt(match[1], 10) === 8 && Number.parseInt(match[2], 10) >= 2))) {
    ok(`${icons.php} PHP ${match[1]}.${match[2]}`);
  } else {
    fail(`${icons.php} PHP ditemukan tapi butuh >= 8.2`);
    errors.push("PHP version terlalu rendah, butuh >= 8.2");
  }
} catch {
  fail(`${icons.php} PHP tidak ditemukan di PATH`);
  errors.push("PHP tidak tersedia");
}

try {
  ok(`🟢 Node ${run("node", ["-v"])}`);
} catch {
  fail("🟢 Node.js tidak ditemukan");
  errors.push("Node.js tidak tersedia");
}

try {
  const composerVersion = run("composer", ["--version"]);
  const match = composerVersion.match(/Composer version (\S+)/);
  ok(`🎼 Composer ${match ? match[1] : "OK"}`);
} catch {
  if (existsSync(resolve(ROOT, "vendor/autoload.php"))) {
    warn("🎼 Composer tidak ditemukan, tapi vendor/ sudah ada jadi dev server tetap bisa jalan");
    warnings.push("Composer tidak tersedia di PATH, tapi vendor sudah terinstall");
  } else {
    fail("🎼 Composer tidak ditemukan");
    errors.push("Composer tidak tersedia");
  }
}

header(icons.files, "Phase 2: Checking Project Files", "bgBlue");

if (existsSync(resolve(ROOT, ".env"))) {
  ok(".env ditemukan");

  const envContent = readFileSync(resolve(ROOT, ".env"), "utf-8");
  const appKeyLine = envContent
    .split("\n")
    .find((line) => line.startsWith("APP_KEY="));

  if (appKeyLine && appKeyLine.length > 10) {
    ok("APP_KEY sudah di-set");
  } else {
    warn("APP_KEY kosong - jalankan: php artisan key:generate");
    warnings.push("APP_KEY belum di-set");
  }
} else {
  warn(".env tidak ada - jalankan: copy .env.example .env");
  warnings.push(".env belum dibuat");
}

if (existsSync(resolve(ROOT, "vendor/autoload.php"))) {
  ok("vendor/ terinstall");
} else {
  fail("vendor/ belum ada - jalankan: composer install");
  errors.push("Composer dependencies belum terinstall");
}

if (existsSync(resolve(ROOT, "node_modules"))) {
  ok("node_modules/ terinstall");
} else {
  fail("node_modules/ belum ada - jalankan: npm install");
  errors.push("npm dependencies belum terinstall");
}

header(icons.database, "Phase 3: Checking Database", "bgMagenta");

const laragonState = startLaragonIfNeeded();

const queueConnection = readEnvValue("QUEUE_CONNECTION") || "database";
info(`Queue connection: ${queueConnection}`);

const databaseResult = tryRun("php", ["artisan", "migrate:status"]);
if (databaseResult.ok) {
  if (databaseResult.output.includes("Pending")) {
    warn("Ada migration yang belum dijalankan");
    warnings.push("Ada pending migrations");
  } else {
    ok("Database & migrations OK");
  }
} else {
  warn("Database belum bisa dicek (pastikan MySQL aktif)");
  warnings.push("Koneksi database gagal dicek");
}

if (queueConnection === "database") {
  ok("Queue database aktif dan akan dijalankan lewat dev launcher");
}

header(icons.tests, "Phase 4: Quick Smoke Test", "bgBlue");

const routeResult = tryRun("php", ["artisan", "route:list", "--json"]);
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

const viewResult = tryRun("php", ["artisan", "view:cache"]);
if (viewResult.ok) {
  ok("Blade views OK");
  tryRun("php", ["artisan", "view:clear"]);
} else {
  warn("Blade views ada masalah");
  warnings.push("View cache gagal");
}

header(icons.tests, "Phase 5: Running Tests", "bgCyan");

if (SHOULD_RUN_TESTS) {
  const testResult = tryRun("php", ["artisan", "test"]);
  if (testResult.ok) {
    const summary = testResult.output
      .split("\n")
      .map((line) => line.trim())
      .filter(Boolean)
      .slice(-3);

    summary.forEach((line) => info(line));
    ok("Semua test PASSED");
  } else {
    const summary = (testResult.output || "")
      .split("\n")
      .map((line) => line.trim())
      .filter(Boolean)
      .slice(-5);

    summary.forEach((line) => info(line));
    warn("Beberapa test gagal (server tetap bisa jalan)");
    warnings.push("Ada test yang gagal");
  }
} else {
  info("Test otomatis dilewati untuk mempercepat startup dev");
  info("Set DEV_RUN_TESTS=true jika ingin selalu menjalankan test saat npm run dev");
}

console.log("");
line();
console.log(`  ${c("bold", "CHECK RESULT SUMMARY")}`);
line();

if (warnings.length > 0) {
  console.log(`\n  ${c("yellow", `Warnings (${warnings.length})`)}`);
  warnings.forEach((warning) => console.log(`    ${c("yellow", icons.warn)} ${warning}`));
}

if (errors.length > 0) {
  console.log(`\n  ${c("red", `Errors (${errors.length})`)}`);
  errors.forEach((error) => console.log(`    ${c("red", icons.fail)} ${error}`));
  console.log(`\n  ${c("red", "Ada error kritikal. Perbaiki dulu sebelum menjalankan server.")}`);
  process.exit(1);
}

if (warnings.length === 0) {
  console.log(`\n  ${c("green", "Semua check PASSED!")}`);
}

console.log("");
header(icons.launch, "Launching Dev Servers", "bgMagenta");
if (laragonState.running) {
  console.log(`  ${c("orange", "🟠 Laragon")}`.padEnd(27, " ") + `${c("white", laragonState.started ? "auto-started" : "already running")}`);
  for (const service of laragonState.services) {
    const label = `└─ ${service.name}`;
    const status = service.running
      ? (service.started ? "started" : "ready")
      : (service.started ? "starting failed" : "not running");

    console.log(`  ${c("orange", label)}`.padEnd(27, " ") + `${c(service.running ? "green" : "yellow", status)}`);
  }
}
console.log(`  ${c("green", `${icons.laravel} Laravel`)}`.padEnd(27, " ") + `${c("white", "http://127.0.0.1:8000")}`);
console.log(`  ${c("magenta", `${icons.vite} Vite`)}`.padEnd(27, " ") + `${c("white", "http://127.0.0.1:5173")}`);
console.log(`  ${c("cyan", `${icons.info} Queue`)}`.padEnd(27, " ") + `${c("white", "php artisan queue:work --tries=1")}`);
if (laragonState.running) {
  console.log(`  ${c("orange", "●")} ${c("bold", "LARAGON")} ${c("gray", "::")} ${c("green", laragonState.started ? "STARTED" : "READY")}`);
}
console.log(`  ${c("green", "●")} ${c("bold", "LARAVEL")}  ${c("gray", "::")} ${c("yellow", "STARTING")}`);
console.log(`  ${c("magenta", "●")} ${c("bold", "VITE")}     ${c("gray", "::")} ${c("yellow", "STARTING")}`);
console.log(`  ${c("cyan", "●")} ${c("bold", "QUEUE")}    ${c("gray", "::")} ${c("yellow", "STARTING")}`);
console.log(`  ${c("orange", "✦")} ${dim("Tekan Ctrl+C untuk stop semua")}`);
console.log("");

const laravel = createProcess("php", ["artisan", "serve"]);
const queue = createProcess("php", ["artisan", "queue:work", "--tries=1"]);
const vite = existsSync(VITE_BIN)
  ? createProcess("node", [VITE_BIN, "--clearScreen", "false"])
  : createProcess("npm", ["run", "dev:vite", "--", "--clearScreen", "false"]);

wireLogs(laravel, {
  label: "LARAVEL",
  icon: icons.laravel,
  color: "green",
  classify: classifyLaravelLine,
});

wireLogs(vite, {
  label: "VITE",
  icon: icons.vite,
  color: "magenta",
  classify: classifyViteLine,
});

wireLogs(queue, {
  label: "QUEUE",
  icon: icons.info,
  color: "cyan",
  classify: classifyQueueLine,
});

function cleanup(signal = "manual") {
  console.log(`\n  ${c("yellow", `${icons.warn} Stopping dev servers (${signal})...`)}`);

  if (!laravel.killed) {
    laravel.kill();
  }

  if (!queue.killed) {
    queue.kill();
  }

  if (!vite.killed) {
    vite.kill();
  }

  process.exit(0);
}

process.on("SIGINT", () => cleanup("SIGINT"));
process.on("SIGTERM", () => cleanup("SIGTERM"));

laravel.on("close", (code) => {
  if (code !== null && code !== 0) {
    console.log(`  ${c("red", `${icons.fail} Laravel exited with code ${code}`)}`);
  }

  if (!queue.killed) {
    queue.kill();
  }

  if (!vite.killed) {
    vite.kill();
  }

  process.exit(code || 0);
});

vite.on("close", (code) => {
  if (code !== null && code !== 0) {
    console.log(`  ${c("red", `${icons.fail} Vite exited with code ${code}`)}`);
  }

  if (!laravel.killed) {
    laravel.kill();
  }

  if (!queue.killed) {
    queue.kill();
  }

  process.exit(code || 0);
});

queue.on("close", (code) => {
  if (code !== null && code !== 0) {
    console.log(`  ${c("red", `${icons.fail} Queue exited with code ${code}`)}`);
  }

  if (!laravel.killed) {
    laravel.kill();
  }

  if (!vite.killed) {
    vite.kill();
  }

  process.exit(code || 0);
});
