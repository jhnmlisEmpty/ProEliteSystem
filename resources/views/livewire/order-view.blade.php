<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-gray-900">Order #ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</h1>
                    <span class="px-3 py-1 rounded border border-gray-300 text-xs font-medium uppercase text-gray-700 bg-white">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                    <span class="px-3 py-1 rounded border border-gray-300 text-xs font-medium uppercase text-gray-700 bg-white">
                        {{ $order->payment_status }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">Created {{ $order->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex gap-3">
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('orders.edit', $order->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">Edit Order</a>
                @endif
                <a href="{{ route('orders.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">Back to Orders</a>
                <button onclick="window.print()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">Print Invoice</button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error') || $errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    @if(session()->has('error'))
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    @endif
                    @if($errors->any())
                        <ul class="text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Revenue (Gross) --}}
        <div class="bg-white border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 uppercase mb-2">Total Revenue (Gross)</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_gross) }}</div>
            <div class="text-xs text-gray-400 mt-1">Includes billable fees</div>
        </div>

        @if(auth()->user()->role === 'admin')
        {{-- Total Expenses --}}
        <div class="bg-white border border-gray-200 p-5">
            <div class="text-xs font-medium text-gray-500 uppercase mb-2">Total Expenses</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_cost) }}</div>
            <div class="text-xs text-gray-400 mt-1">Inventory + Labor</div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-white border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs font-medium text-gray-500 uppercase">Net Profit</div>
                @php
                    $margin = $order->total_gross > 0 ? round(($order->net_income / $order->total_gross) * 100) : 0;
                @endphp
                <span class="text-xs text-gray-500">{{ $margin }}% margin</span>
            </div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->net_income) }}</div>
        </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div class="space-y-4 mb-6">
        {{-- Status Management --}}
        <div class="bg-white border border-gray-200">
            <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Change Status</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-4 gap-2">
                    @foreach($statusOptions as $status)
                        <button 
                            wire:click="changeStatus('{{ $status }}')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm {{ $order->status === $status ? 'bg-gray-900 text-white hover:bg-gray-800' : '' }}"
                        >
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            {{-- Payment Recording --}}
            <div class="bg-white border border-gray-200">
                <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Record Payment</h2>
                </div>
                <div class="p-6">
                    {{-- Payment Breakdown --}}
                    <div class="space-y-2 mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">₱{{ number_format($order->total_gross) }}</span>
                        </div>
                        
                        @if($order->total_gross - $order->total_amount > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Discount ({{ round((($order->total_gross - $order->total_amount) / $order->total_gross) * 100) }}%)</span>
                                <span class="text-gray-900">- ₱{{ number_format($order->total_gross - $order->total_amount) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm font-semibold pt-2 border-t border-gray-100">
                            <span class="text-gray-900">Total Due</span>
                            <span class="text-gray-900">₱{{ number_format($order->total_amount) }}</span>
                        </div>
                        
                        @if($order->payments->count() > 0)
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-xs font-medium text-gray-600 uppercase mb-2">Payment History</p>
                                <div class="space-y-1.5">
                                    @foreach($order->payments as $payment)
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-700">₱{{ number_format($payment->amount) }}</span>
                                                <span class="text-xs text-gray-500">via {{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $payment->created_at->format('M d') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center justify-between text-sm font-medium pt-2 mt-2 border-t border-gray-100">
                                    <span class="text-gray-700">Total Paid</span>
                                    <span class="text-green-600">₱{{ number_format($totalPaid) }}</span>
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm font-bold pt-2 border-t border-gray-100">
                            <span class="text-gray-900">Balance</span>
                            <span class="text-gray-900">₱{{ number_format($remainingBalance) }}</span>
                        </div>
                    </div>

                    @if($remainingBalance > 0)
                        @if(!$showPaymentForm)
                            <div>
                                <button wire:click="togglePaymentForm" class="w-full px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm transition">
                                    Add Payment
                                </button>
                            </div>
                        @else
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Amount (₱)</label>
                                    <input type="number" wire:model="paymentAmount" placeholder="0" min="0" max="{{ $remainingBalance }}" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-gray-400">
                                    @error('paymentAmount') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-400 mt-1">Remaining: ₱{{ number_format($remainingBalance) }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Method</label>
                                    <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-gray-400">
                                        <option value="cash"> Cash</option>
                                        <option value="gcash"> GCash</option>
                                        <option value="bank_transfer"> Bank Transfer</option>
                                        <option value="credit_card"> Credit Card</option>
                                    </select>
                                </div>

                                <!-- <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Note (Optional)</label>
                                    <textarea wire:model="paymentNote" placeholder="Reference number, notes..." rows="2" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-gray-400"></textarea>
                                </div> -->

                                <div class="flex gap-2">
                                    <button wire:click="addPayment" class="flex-1 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm transition">
                                        Confirm
                                    </button>
                                    <button wire:click="togglePaymentForm" class="flex-1 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm transition">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-gray-50 border border-gray-200 p-3">
                            <p class="text-center text-sm text-gray-600">Fully Paid</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer & Vehicle Details --}}
            <div class="bg-white border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Customer & Vehicle Details</h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-600">Customer Name</p>
                            <p class="font-semibold text-gray-900">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Phone</p>
                            <p class="font-semibold text-gray-900">{{ $order->customer?->phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Location</p>
                            <p class="font-semibold text-gray-900">{{ $order->customer?->address ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Vehicle Type</p>
                            <p class="font-semibold text-gray-900">{{ $order->customer?->vehicle_type ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Plate Number</p>
                            <p class="font-semibold text-gray-900">{{ $order->customer?->plate_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
        {{-- Left Column: Order Details --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Products Consumed --}}
            @if($order->orderItems->where('product_id', '!=', null)->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Products Consumed</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                           @foreach($order->orderItems->where('product_id', '!=', null) as $item)
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-600 mt-1">Stock Deducted: {{ $item->quantity }} unit{{ $item->quantity > 1 ? 's' : '' }}</p>
                                        <div class="flex gap-4 mt-2 text-sm">
                                            @if(auth()->user()->role === 'admin')
                                            <span class="text-gray-600">Base Price: <span class="font-medium">₱{{ number_format($item->product->buy_price ?? 0) }}</span></span>
                                            @endif
                                            <span class="text-gray-600">Sell Price: <span class="font-medium">₱{{ number_format($item->product->sell_price ?? 0) }}</span></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₱{{ number_format($item->total_price) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $item->quantity }} × ₱{{ number_format($item->unit_price) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Services Performed --}}
            @if($order->orderItems->where('service_id', '!=', null)->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Services Performed</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($order->orderItems->where('service_id', '!=', null) as $item)
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $item->service->name ?? 'Unknown Service' }}</p>
                                        @php
                                            $crewMembers = $order->serviceAssignments->where('service_id', $item->service_id);
                                        @endphp
                                        @if($crewMembers->count() > 0)
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                @foreach($crewMembers as $assignment)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                                        {{ $assignment->employee->name ?? 'Unknown' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₱{{ number_format($item->total_price) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upholstery Services --}}
            @if($order->orderItems->where('upholstery_id', '!=', null)->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-purple-50 border-purple-200">
                        <h2 class="text-sm font-semibold text-purple-700 uppercase tracking-wide">Upholstery Services</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($order->orderItems->where('upholstery_id', '!=', null) as $item)
                                @php
                                    $upholstery = $item->upholstery;
                                @endphp
                                <div class="border border-purple-100 rounded-lg p-4 bg-purple-50/30">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-lg">{{ $upholstery->unit_type ?? 'N/A' }} - {{ $upholstery->unit_year_model }}</h3>
                                            @if($upholstery->unit_color)
                                                <p class="text-sm text-gray-600 mt-1">Color: {{ $upholstery->unit_color }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900 text-xl">₱{{ number_format($item->total_price) }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">Installation Date</p>
                                            <p class="text-sm text-gray-900">{{ $upholstery->installation_date ? $upholstery->installation_date->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">Services Included</p>
                                            <div class="flex flex-wrap gap-2">
                                                @if($upholstery->services)
                                                    @foreach($upholstery->services as $key => $value)
                                                        @if($value)
                                                            @php
                                                                $serviceLabels = [
                                                                    'seat_cover' => 'Seat Cover',
                                                                    'ceiling' => 'Ceiling',
                                                                    'sidings' => 'Sidings',
                                                                    'rubber_mattings' => 'Rubber Mattings',
                                                                    'front_mattings' => 'Front Mattings',
                                                                ];
                                                            @endphp
                                                            <span class="inline-flex items-center px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded border border-purple-200">
                                                                {{ $serviceLabels[$key] ?? $key }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if($upholstery->description)
                                        <div class="mb-4">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Description</p>
                                            <p class="text-sm text-gray-700 bg-white p-3 rounded border border-gray-200">{{ $upholstery->description }}</p>
                                        </div>
                                    @endif

                                    @if($upholstery->photo_path)
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">Photo</p>
                                            <img src="{{ asset('storage/' . $upholstery->photo_path) }}" alt="Upholstery Photo" class="h-32 w-auto rounded border border-gray-200 shadow-sm">
                                        </div>
                                    @endif

                                    @if($upholstery->downpayment > 0 || $upholstery->balance > 0)
                                        <div class="mt-4 pt-4 border-t border-purple-200">
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                @if($upholstery->downpayment > 0)
                                                    <div>
                                                        <p class="text-xs text-gray-600">Downpayment</p>
                                                        <p class="font-semibold text-gray-900">₱{{ number_format($upholstery->downpayment) }}</p>
                                                    </div>
                                                @endif
                                                @if($upholstery->balance > 0)
                                                    <div>
                                                        <p class="text-xs text-gray-600">Balance</p>
                                                        <p class="font-semibold text-gray-900">₱{{ number_format($upholstery->balance) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Misc. Expenses --}}
            @if($order->expenses->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Misc. Expenses</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($order->expenses as $expense)
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $expense->description }}</p>
                                        <div class="flex gap-4 mt-2 text-sm">
                                            <span class="text-gray-600">My Cost: <span class="font-medium text-red-600">₱{{ number_format($expense->my_cost) }}</span></span>
                                            <span class="text-gray-600">Charge Client: <span class="font-medium text-blue-600">₱{{ number_format($expense->charge_client) }}</span></span>
                                        </div>
                                        @if($expense->is_billable)
                                            <p class="text-xs text-green-600 mt-1">✓ Billable (Charged to client)</p>
                                        @else
                                            <p class="text-xs text-gray-500 mt-1">Internal expense only</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₱{{ number_format($expense->is_billable ? $expense->charge_client : 0) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Revenue Impact</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
