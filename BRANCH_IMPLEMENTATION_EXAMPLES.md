# Example: Adding Branch Filter to Product Management

## Before (Original Code)
```php
// app/Livewire/ProductManagement.php
public function render()
{
    $products = Product::query()
        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(20);

    return view('livewire.product-management', [
        'products' => $products,
    ]);
}
```

## After (With Branch Support)
```php
// app/Livewire/ProductManagement.php
use Livewire\Attributes\On;

public $selectedBranchId = 'all';

public function mount()
{
    // Set default branch for non-admin users
    if (!auth()->user()->isAdmin()) {
        $this->selectedBranchId = auth()->user()->branch_id;
    }
}

#[On('branchChanged')]
public function updateBranch($branchId)
{
    $this->selectedBranchId = $branchId;
    $this->resetPage(); // Reset pagination
}

public function render()
{
    $query = Product::query();
    
    // Branch filtering (Admin can filter, others auto-filtered by global scope)
    if (auth()->user()->isAdmin() && $this->selectedBranchId !== 'all') {
        $query->forBranch($this->selectedBranchId);
    } elseif (auth()->user()->isAdmin() && $this->selectedBranchId === 'all') {
        $query->allBranches();
    }
    
    $products = $query
        ->when($this->search, function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->with('branch') // Include branch info
        ->latest()
        ->paginate(20);

    return view('livewire.product-management', [
        'products' => $products,
    ]);
}
```

## Update the View
```blade
<!-- resources/views/livewire/product-management.blade.php -->
<!-- Add this at the top of your page -->
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Products</h1>
    
    <!-- Branch Selector (only shows for admin) -->
    @livewire('components.branch-selector')
</div>

<!-- In the table, optionally show branch column for admin -->
@if(auth()->user()->isAdmin())
    <th>Branch</th>
@endif

<!-- In table body -->
@if(auth()->user()->isAdmin())
    <td>
        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
            {{ $product->branch->code ?? 'N/A' }}
        </span>
    </td>
@endif
```

---

# Example: Dashboard with Branch Stats

```php
// app/Livewire/Dashboard.php
use Livewire\Attributes\On;

public $selectedBranchId = 'all';

#[On('branchChanged')]
public function updateBranch($branchId)
{
    $this->selectedBranchId = $branchId;
}

public function render()
{
    $query = Order::query();
    
    // Admin can view all or specific branch
    if (auth()->user()->isAdmin()) {
        if ($this->selectedBranchId !== 'all') {
            $query->forBranch($this->selectedBranchId);
        } else {
            $query->allBranches();
        }
    }
    // Non-admin automatically filtered by global scope
    
    $stats = [
        'total_orders' => $query->count(),
        'total_revenue' => $query->sum('total_amount'),
        'pending_orders' => (clone $query)->where('status', 'pending')->count(),
        'completed_orders' => (clone $query)->where('status', 'completed')->count(),
    ];

    return view('livewire.dashboard', compact('stats'));
}
```

---

# Quick Reference: Common Queries

## Get Products from Specific Branch (Admin Only)
```php
$products = Product::forBranch(1)->get();
```

## Get All Products Across All Branches (Admin Only)
```php
$products = Product::allBranches()->get();
```

## Get Current User's Branch Products (Automatic)
```php
$products = Product::all(); // Automatically filtered
```

## Create Product (Auto-assigns to User's Branch)
```php
Product::create([
    'name' => 'New Product',
    'sku' => 'SKU001',
    'sell_price' => 1000,
    // branch_id is automatically set
]);
```

## Create Product for Specific Branch (Admin Only)
```php
Product::create([
    'branch_id' => 2, // Explicit branch
    'name' => 'New Product',
    'sku' => 'SKU001',
    'sell_price' => 1000,
]);
```

---

# Testing Checklist

- [ ] Admin can see all branches in selector
- [ ] Admin can filter by specific branch
- [ ] Admin can view "All Branches"
- [ ] Regular user only sees their branch data
- [ ] Regular user cannot see branch selector
- [ ] New records auto-assign to user's branch
- [ ] Branch isolation works (User A can't see User B's data)
- [ ] Admin can manage all branches in BranchManagement component
