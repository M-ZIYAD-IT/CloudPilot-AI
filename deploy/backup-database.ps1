# Copies the production SQLite database to storage/backups with a
# timestamp, and prunes backups older than $retentionDays. Run daily via
# the "CloudMigrationAdvisor-DailyBackup" scheduled task.

$ErrorActionPreference = "Stop"

$appDir = "C:\Users\SMASHZERO\Desktop\cloud-migration-advisor-production"
$backupDir = "$appDir\storage\backups"
$retentionDays = 14

New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$source = "$appDir\database\database.sqlite"
$destination = "$backupDir\database-$timestamp.sqlite"

Copy-Item -Path $source -Destination $destination -Force

Get-ChildItem -Path $backupDir -Filter "database-*.sqlite" |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$retentionDays) } |
    Remove-Item -Force

Write-Output "Backed up to $destination"
