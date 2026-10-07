param(
    [Parameter(Mandatory = $true)][string]$PhpPath,
    [string]$TaskName = 'BiggGym Scheduler'
)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$phpExecutable = (Resolve-Path -LiteralPath $PhpPath).Path
$artisan = Join-Path $projectRoot 'artisan'
& $phpExecutable -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);'
if ($LASTEXITCODE -ne 0) { throw 'El programador requiere PHP 8.4 o posterior.' }
$action = New-ScheduledTaskAction -Execute $phpExecutable -Argument ('"{0}" schedule:run' -f $artisan) -WorkingDirectory $projectRoot
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -StartWhenAvailable -Hidden
# Runs as the current signed-in user, without storing a password.
$principal = New-ScheduledTaskPrincipal -UserId ([System.Security.Principal.WindowsIdentity]::GetCurrent().Name) -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force | Out-Null
Write-Output "Programador registrado: $TaskName. Se ejecuta mientras esta cuenta tenga sesión iniciada."
