<?php

use App\Models\VillaImage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ops:queue-check {--images=10 : Jumlah image terbaru yang ingin ditampilkan}', function () {
    $queueConnection = (string) config('queue.default');
    $defaultDisk = (string) config('filesystems.default');
    $villaMediaDisk = (string) config('filesystems.villa_media_disk', 'public');
    $imageLimit = max(1, (int) $this->option('images'));

    $this->newLine();
    $this->info('Core PMS Queue Checklist');
    $this->line(str_repeat('-', 72));
    $this->line('APP URL            : '.config('app.url'));
    $this->line('Queue connection   : '.$queueConnection);
    $this->line('Default disk       : '.$defaultDisk);
    $this->line('Villa media disk   : '.$villaMediaDisk);
    $this->newLine();

    $workerCommands = collect(detectQueueWorkersForOps());

    if ($workerCommands->isEmpty()) {
        $this->warn('Queue worker aktif : tidak terdeteksi');
        $this->line('Saran              : jalankan `php artisan queue:work` atau `php artisan queue:listen`');
    } else {
        $this->info('Queue worker aktif : terdeteksi');

        foreach ($workerCommands as $index => $commandLine) {
            $this->line(sprintf('  %d. %s', $index + 1, trim($commandLine)));
        }
    }

    $this->newLine();

    if (! Schema::hasTable('jobs')) {
        $this->error('Tabel `jobs` belum ada. Jalankan migration queue lebih dulu.');

        return self::FAILURE;
    }

    $jobsCount = DB::table('jobs')->count();
    $failedJobsCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
    $oldestJob = DB::table('jobs')->orderBy('id')->first(['id', 'queue', 'created_at', 'available_at']);

    $this->line('Jobs pending       : '.$jobsCount);
    $this->line('Failed jobs        : '.($failedJobsCount === null ? 'tabel tidak ada' : $failedJobsCount));

    if ($oldestJob) {
        $this->line(sprintf(
            'Job terlama        : #%s | queue=%s | created_at=%s',
            $oldestJob->id,
            $oldestJob->queue,
            $oldestJob->created_at ?? '-'
        ));
    } else {
        $this->line('Job terlama        : tidak ada antrean aktif');
    }

    $this->newLine();

    if (! Schema::hasTable('villa_images')) {
        $this->warn('Tabel `villa_images` belum ada, status gallery belum bisa dicek.');

        return self::SUCCESS;
    }

    $statusCounts = VillaImage::query()
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    if ($statusCounts->isEmpty()) {
        $this->line('Belum ada data image villa.');

        return self::SUCCESS;
    }

    $this->info('Ringkasan status image');
    foreach (['pending', 'processing', 'ready', 'failed'] as $status) {
        $this->line(sprintf('- %-10s : %d', $status, (int) ($statusCounts[$status] ?? 0)));
    }

    $this->newLine();
    $this->info("{$imageLimit} image terbaru");

    $recentImages = VillaImage::query()
        ->with('villa:id,name')
        ->latest('id')
        ->take($imageLimit)
        ->get(['id', 'villa_id', 'original_name', 'status', 'processed_at', 'created_at']);

    $rows = $recentImages->map(fn (VillaImage $image) => [
        'ID' => $image->id,
        'Villa' => $image->villa?->name ?? '-',
        'File' => $image->original_name,
        'Status' => $image->status,
        'Processed' => optional($image->processed_at)->format('Y-m-d H:i:s') ?? '-',
        'Created' => optional($image->created_at)->format('Y-m-d H:i:s') ?? '-',
    ])->all();

    $this->table(['ID', 'Villa', 'File', 'Status', 'Processed', 'Created'], $rows);

    $this->newLine();
    if (($statusCounts['pending'] ?? 0) > 0 || ($statusCounts['processing'] ?? 0) > 0) {
        $this->warn('Masih ada image yang belum selesai diproses.');
    } elseif (($statusCounts['failed'] ?? 0) > 0) {
        $this->warn('Tidak ada antrean aktif, tapi masih ada image gagal yang perlu dicek.');
    } else {
        $this->info('Queue image terlihat bersih. Semua image yang terdata sudah selesai diproses.');
    }

    return self::SUCCESS;
})->purpose('Cek worker queue, jumlah job, failed job, dan status image villa terbaru');

if (! function_exists('detectQueueWorkersForOps')) {
    function detectQueueWorkersForOps(): array
    {
        $output = null;

        if (PHP_OS_FAMILY === 'Windows') {
            $command = "powershell -NoProfile -Command \"\$ErrorActionPreference = 'SilentlyContinue'; Get-CimInstance Win32_Process | Where-Object { \$_.Name -eq 'php.exe' -and \$_.CommandLine -match 'queue:(work|listen)' } | Select-Object -ExpandProperty CommandLine\" 2>\$null";
            $output = shell_exec($command);
        } else {
            $output = shell_exec("ps -eo command | grep 'php artisan queue:' | grep -v grep");
        }

        if (! is_string($output) || trim($output) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim($output)))
            ->filter(fn ($line) => trim((string) $line) !== '')
            ->values()
            ->all();
    }
}
