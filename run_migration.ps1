#!/usr/bin/env pwsh
# Script to run the unit name fix migration and verify results

Write-Host "=== Running Unit Name Fix Migration ===" -ForegroundColor Cyan

# Run the migration
docker exec plnweb-app-1 php artisan migrate --path=database/migrations/2026_04_01_210000_fix_unit_names_and_add_missing_units.php --force

Write-Host ""
Write-Host "=== Verifying Unit Names ===" -ForegroundColor Cyan

# Verify results
docker exec plnweb-app-1 php artisan tinker --execute="App\Models\Unit::orderBy('id')->get(['id','code','name'])->each(function(\$u){ echo \$u->id . ' | ' . \$u->code . ' | ' . \$u->name . PHP_EOL; });"

Write-Host ""
Write-Host "=== Running AdminSeeder to create missing users ===" -ForegroundColor Cyan

# Run the AdminSeeder for new users
docker exec plnweb-app-1 php artisan db:seed --class=AdminSeeder --force

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
