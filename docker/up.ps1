$ErrorActionPreference = "Stop"

Set-Location (Split-Path $PSScriptRoot -Parent)

New-Item -ItemType Directory -Force -Path ".docker" | Out-Null

$envFile = ".docker\\stack.env"

if (!(Test-Path $envFile)) {
    $userSuffix = -join (((48..57) + (97..122) | Get-Random -Count 8) | ForEach-Object { [char]$_ })
    $dbUser = "getfy_$userSuffix"
    $dbPass = -join (((48..57) + (65..90) + (97..122) | Get-Random -Count 32) | ForEach-Object { [char]$_ })
    $rootPass = -join (((48..57) + (65..90) + (97..122) | Get-Random -Count 32) | ForEach-Object { [char]$_ })

    @"
GETFY_DB_DATABASE=getfy
GETFY_DB_USERNAME=$dbUser
GETFY_DB_PASSWORD=$dbPass
GETFY_APP_URL=http://localhost
GETFY_HTTP_PORT=80
GETFY_MYSQL_DATABASE=getfy
GETFY_MYSQL_USER=$dbUser
GETFY_MYSQL_PASSWORD=$dbPass
GETFY_MYSQL_ROOT_PASSWORD=$rootPass
"@ | Set-Content -NoNewline -Path $envFile
}

docker compose --env-file $envFile up --build -d
