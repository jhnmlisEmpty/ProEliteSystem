# Branch System Quick Setup

## Step 1: Run Migration
Write-Host "Running migration..." -ForegroundColor Green
php artisan migrate

## Step 2: Seed Sample Data
Write-Host "`nSeeding sample branches and users..." -ForegroundColor Green
php artisan db:seed --class=BranchSeeder

## Step 3: Assign Existing Data (if any)
Write-Host "`nChecking for existing data..." -ForegroundColor Yellow
$response = Read-Host "Do you want to assign existing data to Main Branch? (Y/N)"

if ($response -eq 'Y' -or $response -eq 'y') {
    Write-Host "Assigning existing data to Main Branch..." -ForegroundColor Green
    
    php artisan tinker --execute="
    `$mainBranch = \App\Models\Branch::where('code', 'MAIN')->first();
    if (`$mainBranch) {
        \App\Models\User::whereNull('branch_id')->whereNull('role')->update(['branch_id' => `$mainBranch->id, 'role' => 'user']);
        \App\Models\Product::whereNull('branch_id')->update(['branch_id' => `$mainBranch->id]);
        \App\Models\Customer::whereNull('branch_id')->update(['branch_id' => `$mainBranch->id]);
        \App\Models\Service::whereNull('branch_id')->update(['branch_id' => `$mainBranch->id]);
        \App\Models\Order::whereNull('branch_id')->update(['branch_id' => `$mainBranch->id]);
        echo 'Existing data assigned to Main Branch';
    } else {
        echo 'Main Branch not found. Run seeder first.';
    }
    "
}

Write-Host "`n==================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "`nLogin Credentials:" -ForegroundColor Yellow
Write-Host "Admin: admin@proelite.com / password" -ForegroundColor White
Write-Host "Main Manager: manager.main@proelite.com / password" -ForegroundColor White
Write-Host "Main User: user.main@proelite.com / password" -ForegroundColor White
Write-Host "QC User: user.qc@proelite.com / password" -ForegroundColor White
Write-Host "Makati User: user.makati@proelite.com / password" -ForegroundColor White
Write-Host "`nRead BRANCH_SYSTEM_GUIDE.md for complete documentation" -ForegroundColor Cyan
