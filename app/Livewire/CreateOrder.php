<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
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
    ];

    protected $listeners = [
        'add-product' => 'addProductToCart',
        'add-service' => 'addServiceToCart',
        'add-expense' => 'addExpenseToCart',
        'remove-item' => 'removeItemFromCart',
        'update-discount' => 'updateDiscount',
    ];

    public function mount(): void
    {
        $this->recalculate();
    }

    public function render()
    {
        $customers = $this->customerSearch
            ? Customer::where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->take(8)
                ->get()
            : collect();

        $products = Product::query()
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
            ->when($this->serviceSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->serviceSearch . '%');
            })
            ->orderBy('name')
            ->take(25)
            ->get();

        $employees = Employee::orderBy('name')->get();

        return view('livewire.create-order', [
            'customers' => $customers,
            'products' => $products,
            'services' => $services,
            'employees' => $employees,
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
        $product = Product::find($productId);
        if (!$product) {
            $this->addError('product', 'Product not found.');
            return;
        }

        $quantity = $this->productQuantities[$productId] ?? 1;
        if ($quantity < 1) {
            $quantity = 1;
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
        $this->updateDiscount([
            'type' => $type,
            'value' => (float) $value,
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
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->customerSearch = '';
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

        $customer = Customer::create([
            'name' => $validated['newCustomerName'],
            'phone' => $validated['newCustomerPhone'],
            'address' => $validated['newCustomerAddress'],
        ]);

        $this->selectCustomer($customer->id);
        $this->closeCustomerForm();
    }

    /**
     * DATABASE TRANSACTION MANAGER: Saves the complete order
     */
    public function saveOrder()
    {
        $this->validate();

        if (empty($this->cartItems)) {
            $this->addError('cart', 'Please add items to the order.');
            return;
        }

        try {
            DB::transaction(function () {
                // Create the order
                $order = Order::create([
                    'customer_id' => $this->customer_id,
                    'customer_name' => $this->customer_name,
                    'vehicle_type' => $this->vehicle_type,
                    'plate_number' => $this->plate_number,
                    'type' => $this->getOrderType(),
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'total_amount' => $this->total_due,
                ]);

                // Create order items
                foreach ($this->cartItems as $item) {
                    if ($item['type'] === 'product') {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'service_id' => null,
                            'quantity' => $item['quantity'],
                            'price' => $item['unit_price'],
                        ]);
                    } elseif ($item['type'] === 'service') {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => $item['service_id'],
                            'quantity' => 1,
                            'price' => $item['unit_price'],
                            'crew_members' => json_encode($item['crew_members'] ?? []),
                        ]);
                    } elseif ($item['type'] === 'expense') {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => null,
                            'quantity' => 1,
                            'price' => $item['total_price'],
                            'description' => $item['description'],
                            'is_billable' => $item['is_billable'],
                        ]);
                    }
                }

                session()->flash('success', 'Order created successfully!');
                return $this->redirect(route('order.view', $order->id), navigate: true);
            });
        } catch (\Exception $e) {
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
