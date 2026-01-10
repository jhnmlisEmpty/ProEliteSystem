<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\ProductLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateOrder extends Component
{
    // Customer Information
    public $customer_id = '';
    public $customer_name = '';
    public $vehicle_type = '';
    public $plate_number = '';
    public $customerSearch = '';
    public $branch_id;

    // Master Cart - Single Source of Truth
    public $cartItems = [];
    public $cartProducts = [];
    public $cartServices = [];
    public $cartExpenses = [];

    // Financial Calculations
    public $subtotal = 0;
    public $discount_type = 'percentage'; // 'percentage' or 'fixed'
    public $discount_value = 0;
    public $discounted_amount = 0;
    public $total_due = 0;

    // UI State
    public $showCustomerForm = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerAddress = '';
    public $activeTab = 'products';

    // Product selection
    public $productSearch = '';
    public $productQuantities = [];

    // Service selection
    public $serviceSearch = '';
    public $serviceName = '';
    public $serviceSelectedId = '';
    public $serviceClientPrice = 0;
    public $serviceCrew = [];

    // Expense form
    public $expenseDescription = '';
    public $expenseMyCost = 0;
    public $expenseChargeClient = 0;
    public $expenseBillable = false;

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'customer_name' => 'required|string|min:2',
        'vehicle_type' => 'nullable|string|max:100',
        'plate_number' => 'nullable|string|max:20',
        'branch_id' => 'required|exists:branches,id',
    ];

    protected $listeners = [
        'add-product' => 'addProductToCart',
        'add-service' => 'addServiceToCart',
        'add-expense' => 'addExpenseToCart',
        'remove-item' => 'removeItemFromCart',
        'update-discount' => 'updateDiscount',
    ];
    
    public function mount()
    {
        // Set default branch based on user role
        $user = auth()->user();
        if ($user->role === 'user') {
            $this->branch_id = $user->branch_id;
        }
    }

    public function render()
    {
        // Get current user's branch (admins can see all)
        $userBranch = auth()->user()->branch_id;
        $isAdmin = auth()->user()->role === 'admin';

        $customers = $this->customerSearch
            ? Customer::where(function ($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
                ->take(8)
                ->get()
            : collect();

        $products = Product::query()
            ->when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->when($this->productSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->productSearch . '%')
                        ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
                });
            })
            ->orderBy('name')
            ->take(25)
            ->get();

        $services = Service::query()
            ->when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->when($this->serviceSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->serviceSearch . '%');
            })
            ->orderBy('name')
            ->take(25)
            ->get();

        $employees = Employee::with('user')
            ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
            ->when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->orderBy('name')
            ->get();
        
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $canSelectBranch = in_array(auth()->user()->role, ['admin', 'manager']);

        return view('livewire.create-order', [
            'customers' => $customers,
            'products' => $products,
            'services' => $services,
            'employees' => $employees,
            'branches' => $branches,
            'canSelectBranch' => $canSelectBranch,
        ])->layout('layouts.app');
    }

    /**
     * EVENT LISTENER: Adds a product from ProductSelector child
     */
    public function addProductToCart($data)
    {
        $itemId = $this->makeItemId('product', $data['product_id']);

        $this->cartItems[$itemId] = [
            'id' => $itemId,
            'type' => 'product',
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'quantity' => $data['quantity'] ?? 1,
            'unit_price' => $data['price'],
            'total_price' => ($data['quantity'] ?? 1) * $data['price'],
            'created_at' => now(),
        ];

        $this->recalculate();
    }

    public function addProduct(int $productId): void
    {
        $isAdmin = auth()->user()->role === 'admin';
        $userBranch = auth()->user()->branch_id;
        
        $product = Product::when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->find($productId);
        
        if (!$product) {
            $this->addError('product', 'Product not found or access denied.');
            return;
        }

        $quantity = $this->productQuantities[$productId] ?? 1;
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        if ($quantity > 9999) {
            $this->addError('product', 'Quantity cannot exceed 9999.');
            return;
        }

        $existingKey = $this->findProductItemKey($productId);

        if ($existingKey) {
            // Increase quantity for existing product
            $this->cartItems[$existingKey]['quantity'] += $quantity;
            $this->cartItems[$existingKey]['total_price'] = $this->cartItems[$existingKey]['quantity'] * $this->cartItems[$existingKey]['unit_price'];
        } else {
            $itemId = $this->makeItemId('product', $productId);

            $this->cartItems[$itemId] = [
                'id' => $itemId,
                'type' => 'product',
                'product_id' => $productId,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->sell_price,
                'total_price' => $quantity * $product->sell_price,
                'created_at' => now(),
            ];
        }

        $this->productQuantities[$productId] = 1;
        $this->recalculate();
    }

    public function updateItemQuantity($itemId, $quantity): void
    {
        if (isset($this->cartItems[$itemId])) {
            $quantity = max(1, (int) $quantity);
            $this->cartItems[$itemId]['quantity'] = $quantity;
            $this->cartItems[$itemId]['total_price'] = $quantity * $this->cartItems[$itemId]['unit_price'];
            $this->recalculate();
        }
    }

    public function adjustProductQty(int $productId, int $delta): void
    {
        $current = $this->productQuantities[$productId] ?? 1;
        $new = max(1, $current + $delta);
        $this->productQuantities[$productId] = $new;
    }

    /**
     * EVENT LISTENER: Adds a service from ServiceForm child
     */
    public function addServiceToCart($data)
    {
        $itemId = $this->makeItemId('service', $data['service_id']);

        $this->cartItems[$itemId] = [
            'id' => $itemId,
            'type' => 'service',
            'service_id' => $data['service_id'],
            'name' => $data['name'],
            'quantity' => 1,
            'unit_price' => $data['client_price'],
            'total_price' => $data['client_price'],
            'crew_members' => $data['crew_members'] ?? [],
            'created_at' => now(),
        ];

        $this->recalculate();
    }

    public function addService(): void
    {
        // Validate service name and price
        $validated = $this->validate([
            'serviceName' => 'required|string|min:3',
            'serviceClientPrice' => 'required|numeric|min:0',
        ]);

        $service = $this->resolveService($validated['serviceName']);

        $payload = [
            'service_id' => $service->id,
            'name' => $service->name,
            'client_price' => $this->serviceClientPrice,
            'crew_members' => $this->buildCrewMembers(),
        ];

        $this->addServiceToCart($payload);

        // Clear form
        $this->clearServiceForm();
    }

    public function clearServiceForm(): void
    {
        $this->serviceSelectedId = '';
        $this->serviceName = '';
        $this->serviceClientPrice = 0;
        $this->serviceCrew = [];
    }

    public function selectService($serviceId): void
    {
        $this->serviceSelectedId = $serviceId;
        $service = Service::find($serviceId);
        if ($service) {
            $this->serviceName = $service->name;
            $this->serviceClientPrice = $service->base_labor_cost;
            $this->serviceSearch = '';
        }
    }

    public function toggleCrew(int $userId): void
    {
        if (in_array($userId, $this->serviceCrew, true)) {
            $this->serviceCrew = array_values(array_filter($this->serviceCrew, fn ($id) => $id !== $userId));
        } else {
            $this->serviceCrew[] = $userId;
        }
    }

    /**
     * EVENT LISTENER: Adds an expense from ExpenseForm child
     */
    public function addExpenseToCart($data)
    {
        $itemId = 'expense_' . time();

        $this->cartItems[$itemId] = [
            'id' => $itemId,
            'type' => 'expense',
            'name' => $data['description'],
            'description' => $data['description'],
            'my_cost' => $data['my_cost'],
            'charge_client' => $data['charge_client'],
            'is_billable' => $data['is_billable'],
            'total_price' => $data['is_billable'] ? $data['charge_client'] : 0,
            'created_at' => now(),
        ];

        $this->recalculate();
    }

    public function addExpense(): void
    {
        $validated = $this->validate([
            'expenseDescription' => 'required|string|min:3',
            'expenseMyCost' => 'required|numeric|min:0',
            'expenseChargeClient' => 'nullable|numeric|min:0',
        ]);

        $payload = [
            'description' => $validated['expenseDescription'],
            'my_cost' => $validated['expenseMyCost'],
            'charge_client' => $this->expenseBillable ? ($validated['expenseChargeClient'] ?? 0) : 0,
            'is_billable' => $this->expenseBillable,
        ];

        $this->addExpenseToCart($payload);

        $this->expenseDescription = '';
        $this->expenseMyCost = 0;
        $this->expenseChargeClient = 0;
        $this->expenseBillable = false;
    }

    /**
     * EVENT LISTENER: Removes an item from cart
     */
    public function removeItemFromCart($itemId)
    {
        unset($this->cartItems[$itemId]);
        $this->recalculate();
    }

    /**
     * EVENT LISTENER: Updates discount from OrderSummary child
     */
    public function updateDiscount($data)
    {
        $this->discount_type = $data['type'];
        $this->discount_value = $data['value'];
        $this->recalculate();
    }

    public function setDiscount(string $type, $value): void
    {
        $value = max(0, (float) $value);  // Prevent negative discounts
        $this->updateDiscount([
            'type' => $type,
            'value' => $value,
        ]);
    }

    /**
     * CALCULATOR: Recalculates all totals
     */
    public function recalculate()
    {
        // Calculate Subtotal
        $this->subtotal = array_reduce($this->cartItems, fn ($sum, $item) => $sum + $item['total_price'], 0);

        // Grouped cart for the view
        $this->cartProducts = array_values(array_filter($this->cartItems, fn ($item) => $item['type'] === 'product'));
        $this->cartServices = array_values(array_filter($this->cartItems, fn ($item) => $item['type'] === 'service'));
        $this->cartExpenses = array_values(array_filter($this->cartItems, fn ($item) => $item['type'] === 'expense'));

        // Calculate Discount
        if ($this->discount_type === 'percentage') {
            $this->discounted_amount = round($this->subtotal * ($this->discount_value / 100));
        } else {
            $this->discounted_amount = $this->discount_value;
        }

        // Calculate Total Due
        $this->total_due = max(0, $this->subtotal - $this->discounted_amount);

        // Notify OrderSummary of changes
        $this->dispatch('cart-updated', [
            'items' => $this->cartItems,
            'subtotal' => $this->subtotal,
            'discount' => $this->discounted_amount,
            'total' => $this->total_due,
        ]);
    }

    public function clearCart(): void
    {
        $this->cartItems = [];
        $this->cartProducts = [];
        $this->cartServices = [];
        $this->cartExpenses = [];
        $this->recalculate();
    }

    /**
     * CUSTOMER SELECTION
     */
    public function selectCustomer($customerId)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $userBranch = auth()->user()->branch_id;
        
        $customer = Customer::when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->find($customerId);
        
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->newCustomerName = $customer->name;
            $this->newCustomerPhone = $customer->phone;
            $this->newCustomerAddress = $customer->address;
            $this->customerSearch = '';
        } else {
            $this->addError('customer_id', 'Customer not found or access denied.');
        }
    }

    /**
     * CUSTOMER FORM
     */
    public function openCustomerForm()
    {
        $this->showCustomerForm = true;
    }

    public function closeCustomerForm()
    {
        $this->showCustomerForm = false;
        $this->resetProperties(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function createNewCustomer()
    {
        $validated = $this->validate([
            'newCustomerName' => 'required|string|min:2',
            'newCustomerPhone' => 'required|string',
            'newCustomerAddress' => 'required|string',
        ]);

        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                $customer->update([
                    'name' => $validated['newCustomerName'],
                    'phone' => $validated['newCustomerPhone'],
                    'address' => $validated['newCustomerAddress'],
                ]);
                $this->customer_name = $customer->name;
                $this->customerSearch = '';
            }
        } else {
            $customer = Customer::create([
                'name' => $validated['newCustomerName'],
                'phone' => $validated['newCustomerPhone'],
                'address' => $validated['newCustomerAddress'],
                'branch_id' => auth()->user()->branch_id,
            ]);

            $this->selectCustomer($customer->id);
        }
    }

    /**
     * DATABASE TRANSACTION MANAGER: Saves the complete order with financials
     */
    public function saveOrder()
    {
        $this->validate();

        if (empty($this->cartItems)) {
            $this->addError('cart', 'Please add items to the order.');
            return;
        }

        try {
            $orderId = DB::transaction(function () {
                $customer = Customer::find($this->customer_id);

                if (!$customer) {
                    throw new \Exception('Customer not found.');
                }

                // Create the order
                $order = Order::create([
                    'branch_id' => $this->branch_id,
                    'customer_id' => $this->customer_id,
                    'customer_name' => $this->customer_name,
                    'vehicle_type' => $this->vehicle_type,
                    'plate_number' => $this->plate_number,
                    'type' => $this->getOrderType(),
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'total_amount' => $this->total_due,
                ]);

                // Track financial values
                $totalGross = 0;      // Total revenue from customer
                $totalCost = 0;       // Total cost to us

                // Create order items
                foreach ($this->cartItems as $item) {
                    if ($item['type'] === 'product') {
                        $product = Product::find($item['product_id']);
                        
                        if (!$product) {
                            throw new \Exception("Product {$item['product_id']} not found.");
                        }

                        // Check stock availability
                        if ($product->stock_qty < $item['quantity']) {
                            throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_qty}, Required: {$item['quantity']}");
                        }
                        
                        $itemCost = (int) ($item['quantity'] * $product->buy_price);
                        $itemRevenue = (int) ($item['quantity'] * $item['unit_price']);
                        
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'service_id' => null,
                            'quantity' => $item['quantity'],
                            'unit_price' => (int) $item['unit_price'],
                            'total_price' => $itemRevenue,
                        ]);

                        // Update inventory - deduct stock
                        $oldStock = $product->stock_qty;
                        $product->decrement('stock_qty', $item['quantity']);
                        
                        // Log the inventory change
                        ProductLog::create([
                            'product_id' => $product->id,
                            'change_amount' => -$item['quantity'],
                            'reason' => 'Sale - Order #' . $order->id,
                            'reference_id' => 'ORD-' . $order->id,
                        ]);

                        $totalGross += $itemRevenue;
                        $totalCost += $itemCost;
                    } elseif ($item['type'] === 'service') {
                        $service = Service::find($item['service_id']);
                        
                        if (!$service) {
                            throw new \Exception("Service {$item['service_id']} not found.");
                        }
                        
                        $itemCost = (int) $service->base_labor_cost;  // Service cost to us
                        $itemRevenue = (int) $item['unit_price'];      // Client charge
                        
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => $item['service_id'],
                            'quantity' => 1,
                            'unit_price' => $itemRevenue,
                            'total_price' => $itemRevenue,
                        ]);

                        // Save crew assignments
                        if (!empty($item['crew_members'])) {
                            foreach ($item['crew_members'] as $crewMember) {
                                ServiceAssignment::create([
                                    'order_id' => $order->id,
                                    'service_id' => $item['service_id'],
                                    'employee_id' => $crewMember['id'] ?? $crewMember,
                                ]);
                            }
                        }

                        $totalGross += $itemRevenue;
                        $totalCost += $itemCost;
                    } elseif ($item['type'] === 'expense') {
                        $expenseCost = (int) $item['my_cost'];
                        $expenseCharge = (int) ($item['is_billable'] ? $item['charge_client'] : 0);
                        
                        OrderExpense::create([
                            'order_id' => $order->id,
                            'description' => $item['description'],
                            'my_cost' => $expenseCost,
                            'charge_client' => $expenseCharge,
                            'is_billable' => $item['is_billable'],
                        ]);

                        // Billable expenses count toward revenue
                        if ($item['is_billable']) {
                            $totalGross += $expenseCharge;
                        }
                        
                        // All costs count toward total cost
                        $totalCost += $expenseCost;
                    }
                }

                // Update order with financial totals
                $order->update([
                    'total_gross' => $totalGross,
                    'total_cost' => $totalCost,
                    'net_income' => ($totalGross - $this->discounted_amount) - $totalCost,
                ]);

                return $order->id;
            });

            // Clear cart after successful save
            $this->clearCart();
            
            // Flash success and redirect
            session()->flash('success', 'Order #' . $orderId . ' created successfully!');
            return redirect()->route('orders.view', ['id' => $orderId]);
        } catch (\Exception $e) {
            \Log::error('Order creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->addError('submit', 'Failed to create order: ' . $e->getMessage());
        }
    }

    private function makeItemId(string $type, $id): string
    {
        return $type . '_' . $id . '_' . time();
    }

    private function findProductItemKey(int $productId): ?string
    {
        foreach ($this->cartItems as $key => $item) {
            if ($item['type'] === 'product' && $item['product_id'] === $productId) {
                return $key;
            }
        }

        return null;
    }

    private function resolveService(string $serviceName): Service
    {
        if ($this->serviceSelectedId) {
            $existing = Service::find($this->serviceSelectedId);
            if ($existing) {
                return $existing;
            }
        }

        return Service::create([
            'name' => $serviceName,
            'base_labor_cost' => 0,
        ]);
    }

    private function buildCrewMembers(): array
    {
        $crewMembers = [];

        foreach ($this->serviceCrew as $crewId) {
            $employee = Employee::find($crewId);
            if ($employee) {
                $crewMembers[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                ];
            }
        }

        return $crewMembers;
    }

    /**
     * Helper: Determine order type based on cart items
     */
    private function getOrderType()
    {
        $hasProducts = false;
        $hasServices = false;

        foreach ($this->cartItems as $item) {
            if ($item['type'] === 'product') $hasProducts = true;
            if ($item['type'] === 'service') $hasServices = true;
        }

        if ($hasProducts && $hasServices) return 'both';
        if ($hasServices) return 'service';
        return 'product';
    }
}
