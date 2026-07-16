# Manual stand-in for Forge's push-to-deploy, since there's no git remote
# or CI here. Run this after committing changes in the dev copy that you
# want to promote to production.
#
# Usage: powershell -ExecutionPolicy Bypass -File deploy\redeploy.ps1

$ErrorActionPreference = "Stop"

$appDir = "C:\Users\SMASHZERO\Desktop\cloud-migration-advisor-production"
$nssm = "C:\Users\SMASHZERO\AppData\Local\Microsoft\WinGet\Links\nssm.exe"

Set-Location $appDir

Write-Output "== Pulling latest from local-dev =="
git pull local-dev master

Write-Output "== Installing PHP dependencies =="
composer install --no-dev --optimize-autoloader --no-interaction

Write-Output "== Installing and building frontend assets =="
npm install
npm run build

Write-Output "== Running migrations =="
php artisan migrate --force --no-interaction

Write-Output "== Clearing cached config =="
php artisan config:clear

Write-Output "== Restarting services =="
& $nssm restart CloudMigrationAdvisor-Web
& $nssm restart CloudMigrationAdvisor-Queue

Write-Output "== Done. Tunnel service was left untouched (its public URL would change on restart). =="
