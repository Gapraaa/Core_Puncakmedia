function Get-EnvMap {
    param(
        [Parameter(Mandatory = $true)]
        [string]$EnvPath
    )

    if (-not (Test-Path -LiteralPath $EnvPath)) {
        throw "File .env tidak ditemukan di: $EnvPath"
    }

    $envMap = @{}

    Get-Content -LiteralPath $EnvPath | ForEach-Object {
        $line = $_.Trim()

        if ([string]::IsNullOrWhiteSpace($line) -or $line.StartsWith('#')) {
            return
        }

        $separatorIndex = $line.IndexOf('=')

        if ($separatorIndex -lt 1) {
            return
        }

        $key = $line.Substring(0, $separatorIndex).Trim()
        $value = $line.Substring($separatorIndex + 1).Trim().Trim('"')

        $envMap[$key] = $value
    }

    return $envMap
}

function Get-DbConnectionConfig {
    param(
        [Parameter(Mandatory = $true)]
        [string]$ProjectRoot
    )

    $envMap = Get-EnvMap -EnvPath (Join-Path $ProjectRoot '.env')

    return @{
        Host = if ($envMap.ContainsKey('DB_HOST')) { $envMap['DB_HOST'] } else { '127.0.0.1' }
        Port = if ($envMap.ContainsKey('DB_PORT')) { $envMap['DB_PORT'] } else { '3306' }
        Database = if ($envMap.ContainsKey('DB_DATABASE')) { $envMap['DB_DATABASE'] } else { '' }
        Username = if ($envMap.ContainsKey('DB_USERNAME')) { $envMap['DB_USERNAME'] } else { 'root' }
        Password = if ($envMap.ContainsKey('DB_PASSWORD')) { $envMap['DB_PASSWORD'] } else { '' }
    }
}

function Get-MysqlPasswordArg {
    param(
        [string]$Password
    )

    if ([string]::IsNullOrEmpty($Password)) {
        return $null
    }

    return "-p$Password"
}
