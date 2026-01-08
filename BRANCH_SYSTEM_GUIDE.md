# Multi-Branch Access Control System

## Overview
This system implements a complete multi-branch (multi-tenant) architecture where:
- **Each branch has isolated data** (products, customers, services, orders)
- **Regular users can only see their branch's data**
- **Admin users can view and manage all branches**
- **Data is automatically filtered by branch** using Laravel's global scopes

---

## Database Structure

### New Tables
- `branches` - Stores branch information

### Modified Tables
All core data tables now have a `branch_id` column:
- `users` (also added `role` column)
- `products`
- `customers`
- `services`
- `orders`

---

## User Roles

### 1. Admin (`role = 'admin'`)
- Can access **all branches**
- Can view and manage data from any branch
- Not restricted to a specific branch
- `branch_id` can be `null`

### 2. Manager (`role = 'manager'`)
- Assigned to a specific branch
- Can only access their branch's data
- May have additional permissions (future enhancement)

### 3. User (`role = 'user'`)
- Assigned to a specific branch
- Can only access their branch's data
- Standard employee access

---

## How It Works

### Automatic Data Filtering
The `BelongsToBranch` trait provides:

1. **Auto-assignment on Create**
   ```php
   // When a user creates a product, it's automatically assigned to their branch
   Product::create(['name' => 'New Product']); 
   // branch_id is set automatically
   ```

2. **Auto-filtering on Query**
   ```php
   // Regular users only see their branch's products
   Product::all(); // Returns only products from user's branch
   
   // Admin sees all products
   Product::all(); // Returns all products from all branches
   ```

3. **Manual Branch Filtering**
   ```php
   // Query specific branch (admin only)
   Product::forBranch(2)->get();
   
   // Query all branches (admin only)
   Product::allBranches()->get();
   ```

---

## Installation Steps

### 1. Run the Migration
```bash
php artisan migrate
```

This will:
- Create the `branches` table
- Add `branch_id` to users, products, customers, services, orders
- Add `role` column to users table

### 2. Seed Sample Data (Optional)
```bash
php artisan db:seed --class=BranchSeeder
```

This creates:
- 3 sample branches (Main, QC, Makati)
- 1 admin user (can access all branches)
- 4 branch users (each assigned to specific branches)

**Login Credentials:**
- Admin: `admin@proelite.com` / `password`
- Main Manager: `manager.main@proelite.com` / `password`
- Main User: `user.main@proelite.com` / `password`
- QC User: `user.qc@proelite.com` / `password`
- Makati User: `user.makati@proelite.com` / `password`

### 3. Register Middleware (Optional)
Add to `bootstrap/app.php` or your middleware configuration:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\EnsureBranchAccess::class,
    ]);
})
```

### 4. Assign Existing Data to Branches
If you have existing data, you need to assign it to branches:
```bash
php artisan tinker
```

```php
// Assign all existing users to Main Branch
$mainBranch = \App\Models\Branch::where('code', 'MAIN')->first();
\App\Models\User::whereNull('branch_id')->update(['branch_id' => $mainBranch->id, 'role' => 'user']);

// Assign all existing products to Main Branch
\App\Models\Product::whereNull('branch_id')->update(['branch_id' => $mainBranch->id]);

// Assign all existing customers to Main Branch
\App\Models\Customer::whereNull('branch_id')->update(['branch_id' => $mainBranch->id]);

// Assign all existing services to Main Branch
\App\Models\Service::whereNull('branch_id')->update(['branch_id' => $mainBranch->id]);

// Assign all existing orders to Main Branch
\App\Models\Order::whereNull('branch_id')->update(['branch_id' => $mainBranch->id]);
```

---

## Usage Examples

### Creating New Branch
```php
$branch = Branch::create([
    'name' => 'New Branch',
    'code' => 'NB01',
    'address' => '123 Street Name',
    'phone' => '+63 912 345 6789',
    'is_active' => true,
]);
```

### Creating Users for Branch
```php
User::create([
    'name' => 'Branch Manager',
    'email' => 'manager@branch.com',
    'password' => Hash::make('password'),
    'branch_id' => $branch->id,
    'role' => 'manager',
]);
```

### Admin Panel - View All Branches Data
```php
// In a Livewire component or controller
public function mount()
{
    if (auth()->user()->isAdmin()) {
        // Admin sees all products with branch info
        $this->products = Product::allBranches()
            ->with('branch')
            ->get();
    } else {
        // Regular user sees only their branch
        $this->products = Product::all();
    }
}
```

### Switch Branch (Admin Only)
```php
// In admin panel, show branch selector
public $selectedBranch = null;

public function loadBranchData($branchId)
{
    if (!auth()->user()->isAdmin()) {
        abort(403);
    }
    
    $this->products = Product::forBranch($branchId)->get();
    $this->customers = Customer::forBranch($branchId)->get();
}
```

---

## Adding Branch to New Models

If you create new models that should be branch-specific:

1. **Add migration for branch_id**
   ```php
   Schema::table('new_table', function (Blueprint $table) {
       $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
       $table->index('branch_id');
   });
   ```

2. **Use the trait in model**
   ```php
   use App\Models\Traits\BelongsToBranch;
   
   class NewModel extends Model
   {
       use BelongsToBranch;
       
       protected $fillable = ['branch_id', ...];
   }
   ```

---

## Security Notes

1. **Global Scopes** automatically filter queries - no manual filtering needed
2. **Auto-assignment** ensures new records are tied to correct branch
3. **Admin bypass** allows system admins to manage all branches
4. **Middleware** enforces branch access rules

---

## Testing

### Test Branch Isolation
```bash
php artisan tinker
```

```php
// Login as Main Branch user
Auth::loginUsingId(3); // Main Branch User

// Should only see Main Branch products
Product::all(); // Filtered by branch

// Login as Admin
Auth::loginUsingId(1); // Admin

// Should see all products
Product::all(); // All products from all branches
```

---

## Future Enhancements

1. **Branch Switching UI** - Allow admins to switch context between branches
2. **Branch-Specific Permissions** - Fine-grained permissions per branch
3. **Branch Dashboard** - Analytics per branch
4. **Branch Transfer** - Move data between branches
5. **Branch Settings** - Custom settings per branch

---

## Support

For issues or questions:
1. Check that migrations are run: `php artisan migrate:status`
2. Verify user has branch assigned: `User::find($id)->branch`
3. Test with admin user first
4. Check middleware is registered

---

## Files Created/Modified

### New Files
- `app/Models/Branch.php` - Branch model
- `app/Models/Traits/BelongsToBranch.php` - Global scope trait
- `app/Http/Middleware/EnsureBranchAccess.php` - Middleware
- `database/migrations/2026_01_08_000001_create_branches_and_add_branch_relations.php`
- `database/seeders/BranchSeeder.php`

### Modified Files
- `app/Models/User.php` - Added branch relationship, role methods
- `app/Models/Product.php` - Added branch trait
- `app/Models/Customer.php` - Added branch trait
- `app/Models/Service.php` - Added branch trait
- `app/Models/Order.php` - Added branch trait
