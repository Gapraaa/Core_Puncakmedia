param(
    [string]$OutputFile = ''
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
. (Join-Path $PSScriptRoot 'db-common.ps1')

$mysqldumpExe = (Get-Command mysqldump.exe -ErrorAction Stop).Source
$config = Get-DbConnectionConfig -ProjectRoot $projectRoot
$dumpDirectory = Join-Path $projectRoot 'db_pm_data'

if ([string]::IsNullOrWhiteSpace($config.Database)) {
    throw 'DB_DATABASE di file .env masih kosong.'
}

if (-not (Test-Path -LiteralPath $dumpDirectory)) {
    New-Item -ItemType Directory -Path $dumpDirectory | Out-Null
}

if ([string]::IsNullOrWhiteSpace($OutputFile)) {
    $timestamp = Get-Date -Format 'yyyy-MM-dd_HH-mm-ss'
    $safeDatabase = $config.Database -replace '[^a-zA-Z0-9_-]', '_'
    $outputPath = Join-Path $dumpDirectory "${safeDatabase}_${timestamp}.sql"
} else {
    $outputPath = $OutputFile
}

$passwordArg = Get-MysqlPasswordArg -Password $config.Password
$args = @(
    '--protocol=tcp',
    '--default-character-set=utf8mb4',
    '--single-transaction',
    '--routines',
    '--triggers',
    '-h', $config.Host,
    '-P', $config.Port,
    '-u', $config.Username
)

if ($passwordArg) {
    $args += $passwordArg
}

$args += $config.Database

$dumpContent = & $mysqldumpExe @args
$dumpContent | Set-Content -LiteralPath $outputPath -Encoding UTF8

Write-Host "Export database berhasil."
Write-Host "Source DB  : $($config.Database)@$($config.Host):$($config.Port)"
Write-Host "Dump file  : $outputPath"
