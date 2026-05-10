param(
    [string]$DumpFile = '',
    [switch]$CreateDatabase
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
. (Join-Path $PSScriptRoot 'db-common.ps1')

$mysqlExe = (Get-Command mysql.exe -ErrorAction Stop).Source
$config = Get-DbConnectionConfig -ProjectRoot $projectRoot
$dumpDirectory = Join-Path $projectRoot 'db_pm_data'

if ([string]::IsNullOrWhiteSpace($config.Database)) {
    throw 'DB_DATABASE di file .env masih kosong.'
}

if (-not (Test-Path -LiteralPath $dumpDirectory)) {
    throw "Folder dump database tidak ditemukan: $dumpDirectory"
}

if ([string]::IsNullOrWhiteSpace($DumpFile)) {
    $latestDump = Get-ChildItem -LiteralPath $dumpDirectory -Filter *.sql |
        Sort-Object LastWriteTime -Descending |
        Select-Object -First 1

    if (-not $latestDump) {
        throw "Tidak ada file .sql di folder $dumpDirectory"
    }

    $dumpPath = $latestDump.FullName
} else {
    $dumpPath = Resolve-Path $DumpFile
}

$passwordArg = Get-MysqlPasswordArg -Password $config.Password
$baseArgs = @(
    '--protocol=tcp',
    '--default-character-set=utf8mb4',
    '-h', $config.Host,
    '-P', $config.Port,
    '-u', $config.Username
)

if ($passwordArg) {
    $baseArgs += $passwordArg
}

if ($CreateDatabase) {
    $createDbArgs = @($baseArgs + @('-e', "CREATE DATABASE IF NOT EXISTS ``$($config.Database)`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"))
    & $mysqlExe @createDbArgs
}

$importArgs = @($baseArgs + @($config.Database))
$sqlContent = Get-Content -LiteralPath $dumpPath -Raw -Encoding UTF8

$sqlContent | & $mysqlExe @importArgs

Write-Host "Import database berhasil."
Write-Host "Source dump : $dumpPath"
Write-Host "Target DB   : $($config.Database)@$($config.Host):$($config.Port)"
