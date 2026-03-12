$ErrorActionPreference = "Stop"

Set-Location (Split-Path $PSScriptRoot -Parent)

New-Item -ItemType Directory -Force -Path ".docker" | Out-Null

$envFile = ".docker\\stack.env"

function New-RandomDbUser {
    $userSuffix = -join (((48..57) + (97..122) | Get-Random -Count 8) | ForEach-Object { [char]$_ })
    "getfy_$userSuffix"
}

function New-RandomSecret([int]$len = 32) {
    -join (((48..57) + (65..90) + (97..122) | Get-Random -Count $len) | ForEach-Object { [char]$_ })
}

function Write-EnvFile([string]$path, [hashtable]$vars) {
    $existing = @{}
    if (Test-Path $path) {
        Get-Content $path | ForEach-Object {
            if ($_ -match '^\s*([^#=\s]+)\s*=\s*(.*)\s*$') {
                $existing[$matches[1]] = $matches[2]
            }
        }
    }
    foreach ($k in $vars.Keys) { $existing[$k] = $vars[$k] }
    $content = ($existing.GetEnumerator() | Sort-Object Name | ForEach-Object { "$($_.Name)=$($_.Value)" }) -join "`n"
    Set-Content -NoNewline -Path $path -Value $content
}

if (!(Test-Path $envFile)) {
    $dbUser = New-RandomDbUser
    $dbPass = New-RandomSecret 32
    $rootPass = New-RandomSecret 32
    Write-EnvFile $envFile @{
        GETFY_DB_DATABASE = "getfy"
        GETFY_DB_USERNAME = $dbUser
        GETFY_DB_PASSWORD = $dbPass
        GETFY_APP_URL = "http://localhost"
        GETFY_HTTP_PORT = "80"
        GETFY_MYSQL_DATABASE = "getfy"
        GETFY_MYSQL_USER = $dbUser
        GETFY_MYSQL_PASSWORD = $dbPass
        GETFY_MYSQL_ROOT_PASSWORD = $rootPass
    }
} else {
    $content = Get-Content $envFile -Raw
    $needsRotate = $content -match '^\s*GETFY_DB_USERNAME\s*=\s*(getfy)?\s*$' -or $content -match '^\s*GETFY_DB_PASSWORD\s*=\s*(getfy)?\s*$'
    if ($needsRotate) {
        $dbUser = New-RandomDbUser
        $dbPass = New-RandomSecret 32
        $rootPass = New-RandomSecret 32
        Write-EnvFile $envFile @{
            GETFY_DB_USERNAME = $dbUser
            GETFY_DB_PASSWORD = $dbPass
            GETFY_MYSQL_USER = $dbUser
            GETFY_MYSQL_PASSWORD = $dbPass
            GETFY_MYSQL_ROOT_PASSWORD = $rootPass
        }
    }
}

docker compose --env-file $envFile up --build -d
