<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderEdit extends Component
{
    public int $orderId;
    public ?Order $order = null;

    public $customer_id = '';
    public $customer_name = '';
    public $vehicle_type = '';
    public $plate_number = '';
    public $order_type = 'product';

    public $customerSearch = '';
    public $search = '';
    public $searchType = 'product';

    public $cart = [];

    public $showCustomerForm = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerAddress = '';

    public $showServiceForm = false;
    public $newServiceName = '';
    public $newServiceCost = '';

    /** @var array<int,int> */
    public array $originalProductQuantities = [];
    /** @var array<int,int> */
    public array $originalServiceQuantities = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
    ];

    public function mount(int $id): void
    {
        $this->orderId = $id;
        $this->order = Order::with(['orderItems', 'customer', 'jobOrder', 'payments'])->find($id);

        if (!$this->order) {
            abort(404);
        }

        $this->customer_id = $this->order->customer_id;
        $this->customer_name = $this->order->customer_name;
        $this->vehicle_type = $this->order->vehicle_type ?? '';
        $this->plate_number = $this->order->plate_number ?? '';
        $this->order_type = $this->order->type;

        foreach ($this->order->orderItems as $item) {
            if ($item->product_id) {
                $product = Product::find($item->product_id);
                if (!$product) {
                    continue;
                }

                $availableStock = $product->stock_qty + $item->quantity;
                $this->originalProductQuantities[$product->id] = $item->quantity;
                $cartKey = 'product_' . $product->id;
                $this->cart[$cartKey] = [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'available_stock' => $availableStock,
                ];
            } elseif ($item->service_id) {
                $service = Service::find($item->service_id);
                if (!$service) {
                    continue;
                }

                $this->originalServiceQuantities[$service->id] = $item->quantity;
                $cartKey = 'service_' . $service->id;
                $this->cart[$cartKey] = [
                    'type' => 'service',
                    'id' => $service->id,
                    'name' => $service->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            }
        }
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->customerSearch = '';
        }
    }

    public function toggleCustomerForm(): void
    {
        $this->showCustomerForm = !$this->showCustomerForm;
        $this->resetCustomerForm();
    }

    public function createCustomer(): void
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

        session()->flash('success', 'Customer created and selected.');
    }

    public function resetCustomerForm(): void
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerAddress = '';
    }

    public function toggleServiceForm(): void
    {
        $this->showServiceForm = !$this->showServiceForm;
        $this->resetServiceForm();
    }

    public function createService(): void
    {
        $this->validate([
            'newServiceName' => 'required|max:255',
            'newServiceCost' => 'required|integer|min:0',
        ]);

        $service = Service::create([
            'name' => $this->newServiceName,
            'base_labor_cost' => $this->newServiceCost,
        ]);

        $this->addToCart($service->id, 'service');

        $this->showServiceForm = false;
        $this->resetServiceForm();

        session()->flash('success', 'Service created and added to cart.');
    }

    public function resetServiceForm(): void
    {
        $this->newServiceName = '';
        $this->newServiceCost = '';
    }

    public function addToCart(int $itemId, ?string $type = null): void
    {
        $type = $type ?? $this->searchType;
        $cartKey = $type . '_' . $itemId;

        if ($type === 'product') {
            $product = Product::find($itemId);
            if (!$product) {
                return;
            }

            $originalQty = $this->originalProductQuantities[$product->id] ?? 0;
            $availableStock = max(0, $product->stock_qty + $originalQty);

            if (isset($this->cart[$cartKey])) {
                $newQty = $this->cart[$cartKey]['quantity'] + 1;
                if ($newQty > $availableStock) {
                    session()->flash('error', 'Insufficient stock. Available: ' . $availableStock);
                    return;
                }
                $this->cart[$cartKey]['quantity'] = $newQty;
                $this->cart[$cartKey]['total_price'] = $newQty * $this->cart[$cartKey]['unit_price'];
            } else {
                if ($availableStock < 1) {
                    session()->flash('error', 'Product out of stock.');
                    return;
                }

                $this->cart[$cartKey] = [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => 1,
                    'unit_price' => $product->sell_price,
                    'total_price' => $product->sell_price,
                    'available_stock' => $availableStock,
                ];
            }
        } else {
            $service = Service::find($itemId);
            if (!$service) {
                return;
            }

            if (isset($this->cart[$cartKey])) {
                $newQty = $this->cart[$cartKey]['quantity'] + 1;
                $this->cart[$cartKey]['quantity'] = $newQty;
                $this->cart[$cartKey]['total_price'] = $newQty * $this->cart[$cartKey]['unit_price'];
            } else {
                $this->cart[$cartKey] = [
                    'type' => 'service',
                    'id' => $service->id,
                    'name' => $service->name,
                    'quantity' => 1,
                    'unit_price' => $service->base_labor_cost,
                    'total_price' => $service->base_labor_cost,
                ];
            }
        }

        $this->search = '';
    }

    public function updateQuantity(string $cartKey, $quantity): void
    {
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        $quantity = max(0, (int) $quantity);

        if ($quantity === 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        $item = $this->cart[$cartKey];

        if ($item['type'] === 'product') {
            $product = Product::find($item['id']);
            if (!$product) {
                session()->flash('error', 'Product not found.');
                return;
            }

            $originalQty = $this->originalProductQuantities[$product->id] ?? 0;
            $availableStock = max(0, $product->stock_qty + $originalQty);

            if ($quantity > $availableStock) {
                session()->flash('error', 'Insufficient stock. Available: ' . $availableStock);
                return;
            }

            $this->cart[$cartKey]['available_stock'] = $availableStock;
        }

        $this->cart[$cartKey]['quantity'] = $quantity;
        $this->cart[$cartKey]['total_price'] = $quantity * $this->cart[$cartKey]['unit_price'];
    }

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function getCartTotalProperty(): int
    {
        return (int) collect($this->cart)->sum('total_price');
    }

    public function save(): mixed
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Cart cannot be empty.');
            return null;
        }

        $hasProducts = collect($this->cart)->where('type', 'product')->isNotEmpty();
        $hasServices = collect($this->cart)->where('type', 'service')->isNotEmpty();

        $newOrderType = $hasProducts && $hasServices ? 'both' : ($hasProducts ? 'product' : 'service');

        $newProductQuantities = [];
        foreach ($this->cart as $item) {
            if ($item['type'] === 'product') {
                $newProductQuantities[$item['id']] = $item['quantity'];
            }
        }

        // Validate stock before changes
        foreach ($newProductQuantities as $productId => $newQty) {
            $product = Product::find($productId);
            if (!$product) {
                session()->flash('error', 'Product not found.');
                return null;
            }
            $oldQty = $this->originalProductQuantities[$productId] ?? 0;
            $available = $product->stock_qty + $oldQty;
            if ($newQty > $available) {
                session()->flash('error', 'Insufficient stock for ' . $product->name . '. Available: ' . $available);
                return null;
            }
        }

        DB::beginTransaction();
        try {
            $order = Order::with(['orderItems', 'jobOrder', 'payments'])->findOrFail($this->orderId);

            // Adjust stock based on quantity deltas
            $allProductIds = array_unique(array_merge(array_keys($this->originalProductQuantities), array_keys($newProductQuantities)));
            foreach ($allProductIds as $productId) {
                $product = Product::lockForUpdate()->find($productId);
                if (!$product) {
                    continue;
                }

                $oldQty = $this->originalProductQuantities[$productId] ?? 0;
                $newQty = $newProductQuantities[$productId] ?? 0;
                $delta = $newQty - $oldQty;

                if ($delta !== 0) {
                    if ($delta > 0) {
                        if ($product->stock_qty < $delta) {
                            throw new \RuntimeException('Insufficient stock for ' . $product->name);
                        }
                        $product->decrement('stock_qty', $delta);
                    } else {
                        $product->increment('stock_qty', abs($delta));
                    }

                    ProductLog::create([
                        'product_id' => $product->id,
                        'change_amount' => -$delta,
                        'reason' => 'Order #' . $order->id . ' updated',
                        'reference_id' => 'ORD-' . $order->id,
                    ]);
                }
            }

            // Rebuild order items
            $order->orderItems()->delete();
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Manage job order
            if ($hasServices) {
                if ($order->jobOrder) {
                    // keep existing status/notes
                } else {
                    JobOrder::create([
                        'order_id' => $order->id,
                        'status' => 'pending',
                        'notes' => null,
                    ]);
                }
            } else {
                if ($order->jobOrder) {
                    $order->jobOrder->delete();
                }
            }

            // Update order fields
            $order->update([
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer_name,
                'vehicle_type' => $this->vehicle_type ?: null,
                'plate_number' => $this->plate_number ?: null,
                'type' => $newOrderType,
                'total_amount' => $this->cartTotal,
            ]);

            // Refresh payment status based on new total
            $totalPaid = $order->payments()->sum('amount');
            $newPaymentStatus = $totalPaid >= $order->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');
            if ($order->payment_status !== $newPaymentStatus) {
                $order->update(['payment_status' => $newPaymentStatus]);
            }

            DB::commit();

            session()->flash('success', 'Order #' . $order->id . ' updated successfully.');
            return redirect()->route('orders.view', $order->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to update order: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        $customers = Customer::when($this->customerSearch, function ($q) {
            $q->where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
        })->orderBy('name')->take(10)->get();

        $products = Product::when($this->search && $this->searchType === 'product', function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%');
        })
        ->where('stock_qty', '>', 0)
        ->orderBy('name')
        ->get();

        $services = Service::when($this->search && $this->searchType === 'service', function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->orderBy('name')
        ->get();

        return view('livewire.order-edit', [
            'customers' => $customers,
            'products' => $products,
            'services' => $services,
            'order' => $this->order,
        ])->layout('layouts.app');
    }
}
