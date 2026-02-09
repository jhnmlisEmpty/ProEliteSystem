# ProElite System - AI Coding Agent Instructions

## System Overview
ProElite is a comprehensive business management solution for auto service and product businesses built with **Laravel 12.x + Livewire 3.x + Tailwind CSS 4**. It provides inventory management, POS, order tracking, customer management, and analytics. All prices stored as **integers (pesos, no decimals)**.

## Architecture Patterns

### Multi-Branch Architecture with Global Scopes
**Critical**: Every model uses the `BelongsToBranch` trait which automatically:
- Sets `branch_id` on creation from `Auth::user()->branch_id`
- Applies global scope filtering queries by user's branch (unless admin)
- Admins (role='admin') can see all branches; managers/staff see only their branch

```php
// All models with branch_id use this
use App\Models\Traits\BelongsToBranch;

// Scoping is automatic - queries are already filtered
$products = Product::all(); // Only current branch if not admin
```

### Livewire Component Patterns
**Form Components** follow create/edit pattern:
```php
public function mount(?int $id = null): void {
    if ($id) {
        // Edit mode - load existing record
        $model = Model::findOrFail($id);
        $this->fill($model->toArray());
    }
}

public function save() {
    $validated = $this->validate();
    Model::updateOrCreate(['id' => $this->id], $validated);
    session()->flash('success', 'Record saved!');
    return redirect()->route('models.index');
}
```

**Management Components** use WithPagination:
```php
use WithPagination;
protected $paginationTheme = 'tailwind';

// Search/filters reset pagination
public function updatingSearch() { $this->resetPage(); }
```

### Financial Calculation Pattern (Orders)
Orders use **master cart** pattern with real-time calculation chain:
1. `cartItems` = combined products + services + expenses
2. Calculate `subtotal` from cart totals
3. Apply discount → `discounted_amount`
4. Calculate `total_due` = subtotal - discount
5. Calculate `net_income` = total_due - total_cost (buy prices)

All calculations happen in Livewire component before DB save. See [CreateOrder.php](app/Livewire/CreateOrder.php) lines 376-450.

### Stock Management & Product Logs
**Every stock change** must create a `ProductLog` entry:
```php
// When selling/using products
ProductLog::create([
    'product_id' => $product->id,
    'change_amount' => -$quantity, // Negative for out
    'reason' => 'Sold via Order',
    'reference_id' => "ORD-{$order->id}",
]);
$product->decrement('stock_qty', $quantity);
```

Manual adjustments via [ProductAdjust.php](app/Livewire/ProductAdjust.php) use reasons: "Stock In", "Stock Out", "Damaged", "Adjustment", "Return".

## Development Workflows

### Initial Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed  # Creates 2 branches, admin user, sample data
npm install && npm run build
```

### Development Server
```bash
# Run all services (server, queue, logs, vite)
composer dev

# Or individually:
php artisan serve              # Port 8000
npm run dev                    # Vite hot reload
php artisan queue:listen       # Background jobs
php artisan pail               # Real-time logs
```

### Testing & Code Quality
```bash
composer test          # PHPUnit tests
php artisan pint       # Laravel Pint (PSR-12 formatting)
```

### Database Seeding
Default credentials (see [BranchSeeder.php](database/seeders/BranchSeeder.php)):
- **Admin**: admin@proelite.com / password (all branches)
- **Manager**: manager@proelite.com / password (all branches)
- Creates 2 branches: Baguio (BG01), NCR (NCR01)

## Critical Conventions

### Enum Values
**Order status**: `pending`, `in_progress`, `for_installation`, `completed`, `cancelled`  
**Payment status**: `unpaid`, `partial`, `paid`  
**Product type**: `retail` (has sell_price), `material` (used internally, optional sell_price)  
**Discount type**: `percentage`, `fixed`

### Route Naming
```php
// CRUD pattern: {resource}.{action}
Route::get('/products', ProductManagement::class)->name('products.index');
Route::get('/products/create', ProductForm::class)->name('products.create');
Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit');
```

### Database Conventions
- **Prices**: Integer columns (pesos), no decimal storage
- **Timestamps**: All tables use Laravel `created_at`/`updated_at`
- **Foreign keys**: Cascade deletes except soft-delete scenarios
- **Denormalization**: `orders.customer_name` cached for performance

### View Organization
```
resources/views/livewire/
  {component-name}.blade.php  # Matches App\Livewire\ComponentName
resources/views/components/
  layout.blade.php            # Main layout
  sidebar.blade.php           # Shared sidebar
```

## Integration Points

### Order Creation Flow ([CreateOrder.php](app/Livewire/CreateOrder.php))
1. Select customer (or create inline)
2. Add products/services/expenses to cart
3. Apply discount (percentage or fixed)
4. System calculates: subtotal, discount, total_due, net_income
5. On save: creates Order + OrderItems + ProductLogs + Payment (if provided)
6. Stock decremented atomically via `DB::transaction()`

### Dashboard Analytics ([Dashboard.php](app/Livewire/Dashboard.php))
Real-time metrics use Eloquent queries filtered by branch:
- Total/Monthly Revenue: `Order::sum('total_amount')`
- Low Stock Products: `Product::where('stock_qty', '<=', 'alert_limit')`
- Recent Orders: Latest 5 orders with customer info
- Chart.js integration for visual data

### Kanban Order Board ([OrderManagement.php](app/Livewire/OrderManagement.php))
Drag-and-drop interface updates order status. Uses Livewire events to refresh columns. Toggle between table/kanban views with `$view` property.

## Common Pitfalls

1. **Forgetting branch scope**: Admins bypass automatic filtering - explicitly check `Auth::user()->isAdmin()` when needed
2. **Price calculations**: Always use integers. Display formatting only in views: `{{ number_format($price, 2) }}`
3. **Product type validation**: Retail products require `sell_price`, materials may not have one
4. **Stock logs**: Never modify `product.stock_qty` without creating matching `ProductLog`
5. **Order totals**: Don't recalculate in DB - trust Livewire component calculations before save

## Key Files Reference

- [SYSTEM_DOCUMENTATION.md](SYSTEM_DOCUMENTATION.md) - Complete database schema & business rules
- [app/Models/Traits/BelongsToBranch.php](app/Models/Traits/BelongsToBranch.php) - Global scope implementation
- [app/Livewire/CreateOrder.php](app/Livewire/CreateOrder.php) - Master cart & financial calculations (836 lines)
- [database/seeders/](database/seeders/) - Sample data generation for all entities
- [routes/web.php](routes/web.php) - All application routes with auth middleware

## External Dependencies

- **Livewire 3.7**: Full-stack reactive components
- **Blade Heroicons 2.6**: Icon system via `@svg('heroicon-...')`
- **Chart.js 4.4.0**: Dashboard visualizations
- **Tailwind CSS 4.1**: Utility-first styling with Vite plugin
