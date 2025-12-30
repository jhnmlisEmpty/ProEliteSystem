<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\JobOrder;
use App\Models\ProductLog;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PointOfSale extends Component
{
    public $customer_id = '';
    public $customer_name = '';
    public $vehicle_type = '';
    public $plate_number = '';
    public $order_type = 'product';
    
    public $customerSearch = '';
    public $search = '';
    public $searchType = 'product'; // product or service
    
    public $cart = [];
    
    // Customer creation
    public $showCustomerForm = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerAddress = '';
    
    // Service creation
    public $showServiceForm = false;
    public $newServiceName = '';
    public $newServiceCost = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'order_type' => 'required|in:product,service,both',
    ];

    public function mount()
    {
        $this->cart = [];
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->customerSearch = '';
        }
    }

    public function toggleCustomerForm()
    {
        $this->showCustomerForm = !$this->showCustomerForm;
        $this->resetCustomerForm();
    }

    public function createCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|max:255',
            'newCustomerPhone' => 'required|max:50',
            'newCustomerAddress' => 'required',
        ]);

        $customer = Customer::create([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'address' => $this->newCustomerAddress,
        ]);

        $this->customer_id = $customer->id;
        $this->customer_name = $customer->name;
        $this->showCustomerForm = false;
        $this->resetCustomerForm();
        
        session()->flash('success', 'Customer created successfully!');
    }

    public function resetCustomerForm()
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerAddress = '';
    }

    public function toggleServiceForm()
    {
        $this->showServiceForm = !$this->showServiceForm;
        $this->resetServiceForm();
    }

    public function createService()
    {
        $this->validate([
            'newServiceName' => 'required|max:255',
            'newServiceCost' => 'required|integer|min:0',
        ]);

        $service = Service::create([
            'name' => $this->newServiceName,
            'base_labor_cost' => $this->newServiceCost,
        ]);

        // Automatically add the new service to cart
        $this->addToCart($service->id);

        $this->showServiceForm = false;
        $this->resetServiceForm();
        
        session()->flash('success', 'Service created and added to cart!');
    }

    public function resetServiceForm()
    {
        $this->newServiceName = '';
        $this->newServiceCost = '';
    }

    public function addToCart($itemId)
    {
        $cartKey = $this->searchType . '_' . $itemId;

        if (isset($this->cart[$cartKey])) {
            // Check stock for products
            if ($this->searchType === 'product') {
                $product = Product::find($itemId);
                if ($product && $this->cart[$cartKey]['quantity'] < $product->stock_qty) {
                    $this->cart[$cartKey]['quantity']++;
                    $this->cart[$cartKey]['total_price'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['unit_price'];
                } else {
                    session()->flash('error', 'Insufficient stock available.');
                    return;
                }
            } else {
                $this->cart[$cartKey]['quantity']++;
                $this->cart[$cartKey]['total_price'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['unit_price'];
            }
        } else {
            if ($this->searchType === 'product') {
                $item = Product::find($itemId);
                if (!$item || $item->stock_qty < 1) {
                    session()->flash('error', 'Product out of stock.');
                    return;
                }
                $this->cart[$cartKey] = [
                    'type' => 'product',
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => 1,
                    'unit_price' => $item->sell_price,
                    'total_price' => $item->sell_price,
                    'available_stock' => $item->stock_qty,
                ];
            } else {
                $item = Service::find($itemId);
                if (!$item) return;
                
                $this->cart[$cartKey] = [
                    'type' => 'service',
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => 1,
                    'unit_price' => $item->base_labor_cost,
                    'total_price' => $item->base_labor_cost,
                ];
            }
        }

        $this->search = '';
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity < 1) {
            $this->removeFromCart($cartKey);
            return;
        }

        // Validate stock for products
        if ($this->cart[$cartKey]['type'] === 'product') {
            $product = Product::find($this->cart[$cartKey]['id']);
            if ($product && $quantity > $product->stock_qty) {
                session()->flash('error', 'Insufficient stock. Available: ' . $product->stock_qty);
                return;
            }
        }

        $this->cart[$cartKey]['quantity'] = $quantity;
        $this->cart[$cartKey]['total_price'] = $quantity * $this->cart[$cartKey]['unit_price'];
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum('total_price');
    }

    public function checkout()
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        DB::beginTransaction();
        try {
            // Determine actual order type based on cart contents
            $hasProducts = collect($this->cart)->where('type', 'product')->count() > 0;
            $hasServices = collect($this->cart)->where('type', 'service')->count() > 0;
            
            if ($hasProducts && $hasServices) {
                $actualOrderType = 'both';
            } elseif ($hasProducts) {
                $actualOrderType = 'product';
            } else {
                $actualOrderType = 'service';
            }

            // Create order
            $order = Order::create([
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer_name,
                'vehicle_type' => $this->vehicle_type ?: null,
                'plate_number' => $this->plate_number ?: null,
                'type' => $actualOrderType,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $this->cartTotal,
            ]);

            // Create order items and handle inventory
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Deduct stock and create product log for products
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    $product->decrement('stock_qty', $item['quantity']);

                    ProductLog::create([
                        'product_id' => $product->id,
                        'change_amount' => -$item['quantity'],
                        'reason' => 'Sale - Order #' . $order->id,
                        'reference_id' => 'ORD-' . $order->id,
                    ]);
                }
            }

            // Create job order if services are included
            if ($hasServices) {
                JobOrder::create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'notes' => null,
                ]);
            }

            DB::commit();

            session()->flash('success', 'Order #' . $order->id . ' created successfully! Total: ₱' . number_format($this->cartTotal));
            
            // Reset form
            $this->reset(['customer_id', 'customer_name', 'vehicle_type', 'plate_number', 'order_type', 'search', 'cart']);
            
            return redirect()->route('orders.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function clearCart()
    {
        $this->cart = [];
    }

    public function render()
    {
        // Get filtered customers for search
        $customers = Customer::when($this->customerSearch, function ($q) {
            $q->where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
        })->orderBy('name')->take(10)->get();

        // Get all products or filtered by search
        $products = Product::when($this->search && $this->searchType === 'product', function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%');
        })
        ->where('stock_qty', '>', 0)
        ->orderBy('name')
        ->get();

        // Get all services or filtered by search
        $services = Service::when($this->search && $this->searchType === 'service', function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->orderBy('name')
        ->get();
        
        return view('livewire.point-of-sale', [
            'customers' => $customers,
            'products' => $products,
            'services' => $services,
        ])->layout('layouts.app');
    }
}
