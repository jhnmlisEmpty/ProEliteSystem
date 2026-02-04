<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\Payment;
use App\Models\ProductLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceAssignment;
use App\Models\UpholsteryAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UpholsteryOrder;
use Livewire\WithFileUploads;

class CreateOrder extends Component
{
    use WithFileUploads;

    // Customer Information
    public $customer_id = '';
    public $customer_name = '';
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
    public $newCustomerVehicleType = '';
    public $newCustomerPlateNumber = '';
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

    // Upholstery form
    public $upholsteryYearModel = '';
    public $upholsteryInstallationDate = '';
    public $upholsteryServices = [
        'seat_cover' => false,
        'ceiling' => false,
        'sidings' => false,
        'rubber_mattings' => false,
        'front_mattings' => false,
        'headrest' => false,
    ];
    public $upholsteryDescription = '';
    public $upholsteryPhoto;
    public $upholsteryTotalAmount = 0;
    public $upholstryCrew = [];

    // Upholstery service amounts and descriptions
    public $upholsterySeatCoverAmount = 0;
    public $upholsterySeatCoverDescription = '';
    public $upholsteryCeilingAmount = 0;
    public $upholsteryCeilingDescription = '';
    public $upholstrySidingsAmount = 0;
    public $upholstrySidingsDescription = '';
    public $upholsteryRubberMattingsAmount = 0;
    public $upholsteryRubberMattingsDescription = '';
    public $upholsteryFrontMattingsAmount = 0;
    public $upholsteryFrontMattingsDescription = '';
    public $upholsteryHeadrestAmount = 0;
    public $upholsteryHeadrestDescription = '';

    // Upholstery service field visibility
    public $showSeatCoverFields = false;
    public $showCeilingFields = false;
    public $showSidingsFields = false;
    public $showRubberMattingsFields = false;
    public $showFrontMattingsFields = false;
    public $showHeadrestFields = false;

    // VIP form
    public $vipStepboardPcs = 0;
    public $vipStepboardUnitPrice = 0;
    public $vipStepboardAmount = 0;
    public $vipEngineBayPcs = 0;
    public $vipEngineBayUnitPrice = 0;
    public $vipEngineBayAmount = 0;
    public $vipConsoleBoxPcs = 0;
    public $vipConsoleBoxUnitPrice = 0;
    public $vipConsoleBoxAmount = 0;
    public $vipThaiCeilingPcs = 0;
    public $vipThaiCeilingUnitPrice = 0;
    public $vipThaiCeilingAmount = 0;
    public $vipDescription = '';
    public $vipPhoto;
    public $vipTotalAmount = 0;
    public $vipComponentTotal = 0;

    // VIP package field visibility
    public $showStepboardFields = false;
    public $showEngineBayFields = false;
    public $showConsoleBoxFields = false;
    public $showThaiCeilingFields = false;

    // Quick Payment
    public $showPaymentForm = false;
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $paymentNote = '';
    public $quickPayments = [];

    // Discount Password
    public $showDiscountForm = false;
    public $discountPasswordVerified = false;
    public $discountPassword = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'customer_name' => 'required|string|min:2',
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
        // Automatically select branch for non-admin users
        if ($user->role !== 'admin') {
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
            ->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
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

        $employees = Employee::with('user')
            ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
            ->when(!$isAdmin, fn ($q) => $q->where('branch_id', $userBranch))
            ->orderBy('name')
            ->get();
        
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $canSelectBranch = auth()->user()->role === 'admin';

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

        // Check if branch is selected
        if (!$this->branch_id) {
            $this->addError('product', 'Please select a branch first before adding products.');
            return;
        }

        // Check if product's branch matches the selected branch
        if ($product->branch_id != $this->branch_id) {
            $productBranchName = $product->branch?->name ?? 'unknown branch';
            $selectedBranchName = \App\Models\Branch::find($this->branch_id)?->name ?? 'unknown branch';
            $this->addError('product', "Cannot add product from '{$productBranchName}' to order in '{$selectedBranchName}'. Products must be from the same branch.");
            return;
        }

        // Check if product has stock
        if (($product->stock_qty ?? 0) <= 0) {
            $this->addError('product', "'{$product->name}' is out of stock and cannot be added.");
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

    public function toggleUpholstryCrew(int $userId): void
    {
        $found = false;
        $this->upholstryCrew = array_filter($this->upholstryCrew, function ($member) use ($userId, &$found) {
            if (is_array($member) && ($member['id'] ?? null) === $userId) {
                $found = true;
                return false;
            }
            return true;
        });

        if (!$found) {
            $employee = Employee::with('user')->find($userId);
            if ($employee && $employee->user) {
                $this->upholstryCrew[] = ['id' => $employee->id, 'name' => $employee->user->name];
            }
        }

        $this->upholstryCrew = array_values($this->upholstryCrew);
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

    public function addUpholstery(): void
    {
        // Calculate total from individual service amounts - cast to int to handle form string inputs
        $serviceTotal = (int)$this->upholsterySeatCoverAmount + (int)$this->upholsteryCeilingAmount + (int)$this->upholstrySidingsAmount + 
                       (int)$this->upholsteryRubberMattingsAmount + (int)$this->upholsteryFrontMattingsAmount + (int)$this->upholsteryHeadrestAmount;

        $this->upholsteryTotalAmount = $serviceTotal;

        $this->validate([
            'upholsteryYearModel' => 'required|string',
            'upholsteryInstallationDate' => 'required|date',
            'upholsteryTotalAmount' => 'required|integer|min:1',
        ], [
            'upholsteryYearModel.required' => 'Year model is required',
            'upholsteryInstallationDate.required' => 'Installation date is required',
            'upholsteryTotalAmount.required' => 'Total amount is required',
            'upholsteryTotalAmount.min' => 'Total amount must be at least 1',
        ]);

        // Check if at least one service is selected
        $hasService = false;
        foreach ($this->upholsteryServices as $service => $selected) {
            if ($selected) {
                $hasService = true;
                break;
            }
        }

        if (!$hasService) {
            $this->addError('upholsteryServices', 'Please select at least one service');
            return;
        }

        $itemId = 'upholstery_' . time();

        // Get selected services labels
        $serviceLabels = [
            'seat_cover' => 'Seat Cover',
            'ceiling' => 'Ceiling',
            'sidings' => 'Sidings',
            'rubber_mattings' => 'Rubber Mattings',
            'front_mattings' => 'Front Mattings',
            'headrest' => 'Headrest',
        ];

        $selectedServices = [];
        foreach ($this->upholsteryServices as $key => $selected) {
            if ($selected) {
                $selectedServices[] = $serviceLabels[$key];
            }
        }

        $this->cartItems[$itemId] = [
            'id' => $itemId,
            'type' => 'upholstery',
            'name' => 'Upholstery - ' . $this->upholsteryYearModel . ' (' . implode(', ', $selectedServices) . ')',
            'year_model' => $this->upholsteryYearModel,
            'installation_date' => $this->upholsteryInstallationDate,
            'services' => $this->upholsteryServices,
            'description' => $this->upholsteryDescription,
            'photo' => $this->upholsteryPhoto,
            'seat_cover_amount' => $this->upholsteryServices['seat_cover'] ? (int)$this->upholsterySeatCoverAmount : 0,
            'seat_cover_description' => $this->upholsteryServices['seat_cover'] ? $this->upholsterySeatCoverDescription : '',
            'ceiling_amount' => $this->upholsteryServices['ceiling'] ? (int)$this->upholsteryCeilingAmount : 0,
            'ceiling_description' => $this->upholsteryServices['ceiling'] ? $this->upholsteryCeilingDescription : '',
            'sidings_amount' => $this->upholsteryServices['sidings'] ? (int)$this->upholstrySidingsAmount : 0,
            'sidings_description' => $this->upholsteryServices['sidings'] ? $this->upholstrySidingsDescription : '',
            'rubber_mattings_amount' => $this->upholsteryServices['rubber_mattings'] ? (int)$this->upholsteryRubberMattingsAmount : 0,
            'rubber_mattings_description' => $this->upholsteryServices['rubber_mattings'] ? $this->upholsteryRubberMattingsDescription : '',
            'front_mattings_amount' => $this->upholsteryServices['front_mattings'] ? (int)$this->upholsteryFrontMattingsAmount : 0,
            'front_mattings_description' => $this->upholsteryServices['front_mattings'] ? $this->upholsteryFrontMattingsDescription : '',
            'headrest_amount' => $this->upholsteryServices['headrest'] ? (int)$this->upholsteryHeadrestAmount : 0,
            'headrest_description' => $this->upholsteryServices['headrest'] ? $this->upholsteryHeadrestDescription : '',
            'unit_price' => $this->upholsteryTotalAmount,
            'total_price' => $this->upholsteryTotalAmount,
            'quantity' => 1,
            'crew_members' => $this->upholstryCrew,
            'created_at' => now(),
        ];

        $this->recalculate();
        $this->clearUpholsteryForm();
    }

    public function clearUpholsteryForm(): void
    {
        $this->upholsteryYearModel = '';
        $this->upholsteryInstallationDate = '';
        $this->upholsteryServices = [
            'seat_cover' => false,
            'ceiling' => false,
            'sidings' => false,
            'rubber_mattings' => false,
            'front_mattings' => false,
            'headrest' => false,
        ];
        $this->upholsteryDescription = '';
        $this->upholsteryPhoto = null;
        $this->upholsteryTotalAmount = 0;
        $this->upholstryCrew = [];
        $this->upholsterySeatCoverAmount = 0;
        $this->upholsterySeatCoverDescription = '';
        $this->upholsteryCeilingAmount = 0;
        $this->upholsteryCeilingDescription = '';
        $this->upholstrySidingsAmount = 0;
        $this->upholstrySidingsDescription = '';
        $this->upholsteryRubberMattingsAmount = 0;
        $this->upholsteryRubberMattingsDescription = '';
        $this->upholsteryFrontMattingsAmount = 0;
        $this->upholsteryFrontMattingsDescription = '';
        $this->upholsteryHeadrestAmount = 0;
        $this->upholsteryHeadrestDescription = '';
        $this->showSeatCoverFields = false;
        $this->showCeilingFields = false;
        $this->showSidingsFields = false;
        $this->showRubberMattingsFields = false;
        $this->showFrontMattingsFields = false;
        $this->showHeadrestFields = false;
        $this->resetErrorBag();
    }

    public function addVip(): void
    {
        // Calculate component total from sub-amounts
        $componentTotal = $this->vipStepboardAmount + $this->vipEngineBayAmount + $this->vipConsoleBoxAmount + $this->vipThaiCeilingAmount;

        // Validate that at least one component is selected
        if ($componentTotal <= 0) {
            $this->addError('vipComponents', 'Please add at least one VIP component with amount > 0');
            return;
        }

        $this->validate([
            'vipStepboardPcs' => 'required|integer|min:0',
            'vipStepboardUnitPrice' => 'required|integer|min:0',
            'vipEngineBayPcs' => 'required|integer|min:0',
            'vipEngineBayUnitPrice' => 'required|integer|min:0',
            'vipConsoleBoxPcs' => 'required|integer|min:0',
            'vipConsoleBoxUnitPrice' => 'required|integer|min:0',
            'vipThaiCeilingPcs' => 'required|integer|min:0',
            'vipThaiCeilingUnitPrice' => 'required|integer|min:0',
            'vipTotalAmount' => 'required|integer|min:' . $componentTotal,
        ], [
            'vipTotalAmount.min' => 'Total amount cannot be lower than the sum of all components (₱' . $componentTotal . ')',
        ]);

        $itemId = 'vip_' . time();

        // Upload photo if provided
        $photoPath = null;
        if ($this->vipPhoto) {
            $photoPath = $this->vipPhoto->store('vip-photos', 'public');
        }

        $this->cartItems[$itemId] = [
            'id' => $itemId,
            'type' => 'vip',
            'name' => 'VIP Package',
            'stepboard_pcs' => (int) $this->vipStepboardPcs,
            'stepboard_unit_price' => (int) $this->vipStepboardUnitPrice,
            'stepboard_amount' => (int) $this->vipStepboardAmount,
            'engine_bay_pcs' => (int) $this->vipEngineBayPcs,
            'engine_bay_unit_price' => (int) $this->vipEngineBayUnitPrice,
            'engine_bay_amount' => (int) $this->vipEngineBayAmount,
            'console_box_pcs' => (int) $this->vipConsoleBoxPcs,
            'console_box_unit_price' => (int) $this->vipConsoleBoxUnitPrice,
            'console_box_amount' => (int) $this->vipConsoleBoxAmount,
            'thai_ceiling_pcs' => (int) $this->vipThaiCeilingPcs,
            'thai_ceiling_unit_price' => (int) $this->vipThaiCeilingUnitPrice,
            'thai_ceiling_amount' => (int) $this->vipThaiCeilingAmount,
            'description' => $this->vipDescription ?? '',
            'photo' => $photoPath,
            'unit_price' => (int) $this->vipTotalAmount,
            'total_price' => (int) $this->vipTotalAmount,
            'quantity' => 1,
            'created_at' => now(),
        ];

        $this->recalculate();
        $this->clearVipForm();
    }

    public function clearVipForm(): void
    {
        $this->vipStepboardPcs = 0;
        $this->vipStepboardUnitPrice = 0;
        $this->vipStepboardAmount = 0;
        $this->vipEngineBayPcs = 0;
        $this->vipEngineBayUnitPrice = 0;
        $this->vipEngineBayAmount = 0;
        $this->vipConsoleBoxPcs = 0;
        $this->vipConsoleBoxUnitPrice = 0;
        $this->vipConsoleBoxAmount = 0;
        $this->vipThaiCeilingPcs = 0;
        $this->vipThaiCeilingUnitPrice = 0;
        $this->vipThaiCeilingAmount = 0;
        $this->vipDescription = '';
        $this->vipPhoto = null;
        $this->vipTotalAmount = 0;
        $this->vipComponentTotal = 0;
        $this->showStepboardFields = false;
        $this->showEngineBayFields = false;
        $this->showConsoleBoxFields = false;
        $this->showThaiCeilingFields = false;
        $this->resetErrorBag();
    }

    public function calculateVipComponentTotal(): void
    {
        // Calculate sub-amounts for each component - cast to int to handle form string inputs
        $this->vipStepboardAmount = (int)$this->vipStepboardPcs * (int)$this->vipStepboardUnitPrice;
        $this->vipEngineBayAmount = (int)$this->vipEngineBayPcs * (int)$this->vipEngineBayUnitPrice;
        $this->vipConsoleBoxAmount = (int)$this->vipConsoleBoxPcs * (int)$this->vipConsoleBoxUnitPrice;
        $this->vipThaiCeilingAmount = (int)$this->vipThaiCeilingPcs * (int)$this->vipThaiCeilingUnitPrice;
        
        // Calculate total component total
        $this->vipComponentTotal = $this->vipStepboardAmount + $this->vipEngineBayAmount + $this->vipConsoleBoxAmount + $this->vipThaiCeilingAmount;
        
        // Auto-update total amount to match component total
        $this->vipTotalAmount = $this->vipComponentTotal;
    }

    public function calculateUpholsteryTotal(): void
    {
        // Calculate total from individual service amounts - cast to int to handle form string inputs
        $this->upholsteryTotalAmount = (int)$this->upholsterySeatCoverAmount + (int)$this->upholsteryCeilingAmount + (int)$this->upholstrySidingsAmount + 
                                      (int)$this->upholsteryRubberMattingsAmount + (int)$this->upholsteryFrontMattingsAmount + (int)$this->upholsteryHeadrestAmount;
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
            $this->newCustomerVehicleType = $customer->vehicle_type;
            $this->newCustomerPlateNumber = $customer->plate_number;
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
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedBranchId(): void
    {
        // Reset product quantity inputs when branch changes
        $this->productQuantities = [];
    }

    public function updatedUpholsterySeatCoverAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedUpholsteryCeilingAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedUpholstrySidingsAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedUpholsteryRubberMattingsAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedUpholsteryFrontMattingsAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedUpholsteryHeadrestAmount(): void
    {
        $this->calculateUpholsteryTotal();
    }

    public function updatedVipStepboardPcs(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipStepboardUnitPrice(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipEngineBayPcs(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipEngineBayUnitPrice(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipConsoleBoxPcs(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipConsoleBoxUnitPrice(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipThaiCeilingPcs(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function updatedVipThaiCeilingUnitPrice(): void
    {
        $this->calculateVipComponentTotal();
    }

    public function createNewCustomer()
    {
        $validated = $this->validate([
            'newCustomerName' => 'required|string|min:2',
            'newCustomerPhone' => 'required|string',
            'newCustomerAddress' => 'nullable|string',
            'newCustomerVehicleType' => 'nullable|string|max:255',
            'newCustomerPlateNumber' => 'nullable|string|max:255',
        ]);

        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                $customer->update([
                    'name' => $validated['newCustomerName'],
                    'phone' => $validated['newCustomerPhone'],
                    'address' => $validated['newCustomerAddress'],
                    'vehicle_type' => $validated['newCustomerVehicleType'],
                    'plate_number' => $validated['newCustomerPlateNumber'],
                ]);
                $this->customer_name = $customer->name;
                $this->customerSearch = '';
            }
        } else {
            $customer = Customer::create([
                'name' => $validated['newCustomerName'],
                'phone' => $validated['newCustomerPhone'],
                'address' => $validated['newCustomerAddress'],
                'vehicle_type' => $validated['newCustomerVehicleType'],
                'plate_number' => $validated['newCustomerPlateNumber'],
                'branch_id' => auth()->user()->branch_id,
            ]);

            $this->selectCustomer($customer->id);
        }
    }

    /**
     * QUICK PAYMENT METHODS
     */
    public function addQuickPayment()
    {
        if (empty($this->paymentAmount)) {
            $this->addError('paymentAmount', 'Please enter a payment amount.');
            return;
        }

        if ($this->paymentAmount <= 0) {
            $this->addError('paymentAmount', 'Payment amount must be greater than 0.');
            return;
        }

        // Calculate total payments already added
        $totalPaymentsAlready = collect($this->quickPayments)->sum('amount');
        
        // Calculate remaining balance
        $remainingBalance = $this->total_due - $totalPaymentsAlready;
        
        // Check if new payment exceeds remaining balance
        if ($this->paymentAmount > $remainingBalance) {
            $this->addError('paymentAmount', 'Payment amount exceeds remaining balance of ₱' . number_format($remainingBalance, 2) . '. Total already paid: ₱' . number_format($totalPaymentsAlready, 2));
            return;
        }

        // Add payment to quick payments array
        $this->quickPayments[] = [
            'amount' => (float) $this->paymentAmount,
            'method' => $this->paymentMethod,
            'note' => $this->paymentNote,
        ];

        // Reset form
        $this->paymentAmount = '';
        $this->paymentMethod = 'cash';
        $this->paymentNote = '';
        $this->resetErrorBag();
    }

    public function removeQuickPayment($index)
    {
        unset($this->quickPayments[$index]);
        $this->quickPayments = array_values($this->quickPayments); // Reindex array
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
                    'type' => $this->getOrderType(),
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'discount_type' => $this->discount_type,
                    'discount_value' => $this->discount_value,
                    'discounted_amount' => $this->discounted_amount,
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
                        // $totalCost += $itemCost; Excluding service labor cost from total cost for now
                    } elseif ($item['type'] === 'upholstery') {
                        $itemRevenue = (int) $item['unit_price'];
                        
                        // Upload photo if provided
                        $photoPath = null;
                        if (isset($item['photo']) && $item['photo']) {
                            $photoPath = $item['photo']->store('upholstery-photos', 'public');
                        }
                        
                        // Create the UpholsteryOrder record
                        $upholstery = UpholsteryOrder::create([
                            'order_id' => $order->id,
                            'unit_type' => $this->newCustomerVehicleType ?? 'Unknown',
                            'unit_year_model' => $item['year_model'],
                            'unit_color' => '',  // Can be added later if needed
                            'services' => $item['services'],
                            'description' => $item['description'] ?? '',
                            'photo_path' => $photoPath,
                            'installation_date' => $item['installation_date'],
                            'seat_cover_amount' => $item['seat_cover_amount'] ?? 0,
                            'seat_cover_description' => $item['seat_cover_description'] ?? '',
                            'ceiling_amount' => $item['ceiling_amount'] ?? 0,
                            'ceiling_description' => $item['ceiling_description'] ?? '',
                            'sidings_amount' => $item['sidings_amount'] ?? 0,
                            'sidings_description' => $item['sidings_description'] ?? '',
                            'rubber_mattings_amount' => $item['rubber_mattings_amount'] ?? 0,
                            'rubber_mattings_description' => $item['rubber_mattings_description'] ?? '',
                            'front_mattings_amount' => $item['front_mattings_amount'] ?? 0,
                            'front_mattings_description' => $item['front_mattings_description'] ?? '',
                            'headrest_amount' => $item['headrest_amount'] ?? 0,
                            'headrest_description' => $item['headrest_description'] ?? '',
                            'downpayment' => 0,  // Will be updated after payments
                            'balance' => $itemRevenue,  // Will be updated after discount and payments
                        ]);
                        
                        // Create OrderItem linked to the upholstery
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => null,
                            'upholstery_id' => $upholstery->id,
                            'quantity' => 1,
                            'unit_price' => $itemRevenue,
                            'total_price' => $itemRevenue,
                        ]);

                        // Save crew assignments for upholstery
                        if (!empty($item['crew_members'])) {
                            foreach ($item['crew_members'] as $crewMember) {
                                UpholsteryAssignment::create([
                                    'order_id' => $order->id,
                                    'upholstery_id' => $upholstery->id,
                                    'employee_id' => $crewMember['id'] ?? $crewMember,
                                ]);
                            }
                        }

                        $totalGross += $itemRevenue;
                        // Upholstery has no direct cost in this model
                    } elseif ($item['type'] === 'vip') {
                        $itemRevenue = (int) $item['unit_price'];
                        
                        // Photo path is already stored in addVip(), just use it directly
                        $photoPath = $item['photo'] ?? null;
                        
                        // Create the VIP record
                        $vip = \App\Models\Vip::create([
                            'stepboard_pcs' => (int) ($item['stepboard_pcs'] ?? 0),
                            'stepboard_unit_price' => (int) ($item['stepboard_unit_price'] ?? 0),
                            'stepboard_amount' => (int) ($item['stepboard_amount'] ?? 0),
                            'engine_bay_pcs' => (int) ($item['engine_bay_pcs'] ?? 0),
                            'engine_bay_unit_price' => (int) ($item['engine_bay_unit_price'] ?? 0),
                            'engine_bay_amount' => (int) ($item['engine_bay_amount'] ?? 0),
                            'console_box_pcs' => (int) ($item['console_box_pcs'] ?? 0),
                            'console_box_unit_price' => (int) ($item['console_box_unit_price'] ?? 0),
                            'console_box_amount' => (int) ($item['console_box_amount'] ?? 0),
                            'thai_ceiling_pcs' => (int) ($item['thai_ceiling_pcs'] ?? 0),
                            'thai_ceiling_unit_price' => (int) ($item['thai_ceiling_unit_price'] ?? 0),
                            'thai_ceiling_amount' => (int) ($item['thai_ceiling_amount'] ?? 0),
                            'description' => $item['description'] ?? '',
                            'photo' => $photoPath,
                            'total_amount' => $itemRevenue,
                        ]);
                        
                        // Create OrderItem linked to the VIP
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => null,
                            'upholstery_id' => null,
                            'vip_id' => $vip->id,
                            'quantity' => 1,
                            'unit_price' => $itemRevenue,
                            'total_price' => $itemRevenue,
                        ]);

                        $totalGross += $itemRevenue;
                        // VIP has no direct cost in this model
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

                // Calculate total paid from quick payments
                $totalPaid = 0;
                if (!empty($this->quickPayments)) {
                    foreach ($this->quickPayments as $payment) {
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $payment['amount'],
                            'method' => $payment['method'],
                            'reference' => $payment['note'],
                            'paid_at' => now(),
                        ]);
                        $totalPaid += $payment['amount'];
                    }
                    
                    // Update payment status based on total paid
                    if ($totalPaid >= $this->total_due) {
                        $order->update(['payment_status' => 'paid']);
                    } elseif ($totalPaid > 0) {
                        $order->update(['payment_status' => 'partial']);
                    }
                }

                // Update upholstery balance after payments
                $this->updateUpholsteryBalances($order, $totalPaid);

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
     * Update upholstery balance based on order total and payments
     */
    private function updateUpholsteryBalances($order, $totalPaid)
    {
        $upholsteryItems = OrderItem::where('order_id', $order->id)
            ->where('upholstery_id', '!=', null)
            ->with('upholstery')
            ->get();

        if ($upholsteryItems->isEmpty()) {
            return;
        }

        $orderTotalGross = $order->total_gross;
        $orderTotalAmount = $order->total_amount; // After discount

        // Update each upholstery record proportionally
        foreach ($upholsteryItems as $item) {
            if ($item->upholstery) {
                // Calculate this upholstery item's proportion of the order
                $upholsteryProportion = $orderTotalGross > 0 
                    ? $item->total_price / $orderTotalGross 
                    : 0;

                // Calculate upholstery amount after discount
                $upholsteryAfterDiscount = $upholsteryProportion * $orderTotalAmount;

                // Calculate how much has been paid towards this upholstery
                $upholsteryPaidAmount = $upholsteryProportion * $totalPaid;

                // Calculate remaining balance
                $upholsteryBalance = max(0, $upholsteryAfterDiscount - $upholsteryPaidAmount);

                $item->upholstery->update([
                    'balance' => (int) round($upholsteryBalance),
                    'downpayment' => (int) round($upholsteryPaidAmount),
                ]);
            }
        }
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

    public function toggleDiscountForm()
    {
        $this->showDiscountForm = !$this->showDiscountForm;
        if (!$this->showDiscountForm) {
            $this->resetDiscountForm();
        }
    }

    public function verifyDiscountPassword()
    {
        $this->validate([
            'discountPassword' => 'required|string',
        ]);

        // Find any admin user and verify password
        $adminUser = User::where('role', 'admin')
            ->orWhere('role', 'Admin')
            ->first();

        if ($adminUser && Hash::check($this->discountPassword, $adminUser->password)) {
            $this->discountPasswordVerified = true;
            $this->discountPassword = '';
        } else {
            $this->addError('discountPassword', 'Invalid admin password');
        }
    }

    public function resetDiscountForm()
    {
        $this->showDiscountForm = false;
        $this->discountPasswordVerified = false;
        $this->discountPassword = '';
        $this->discount_value = 0;
        $this->discount_amount = 0;
        $this->recalculate();

    }
}
