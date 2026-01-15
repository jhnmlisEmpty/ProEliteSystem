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
                <p class="text-sm text-gray-600 mt-2">Created {{ $order->created_at->format('M d, Y') }} • <span class="font-semibold text-gray-900">Branch: {{ $order->branch->name ?? 'N/A' }}</span></p>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        {{-- Total Revenue (Gross) --}}
        <div class="bg-white border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Revenue (Gross)</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_gross) }}</div>
            <div class="text-xs text-gray-400 mt-1">Includes billable fees</div>
        </div>

        @if(auth()->user()->role === 'admin')
        {{-- Total Expenses --}}
        <div class="bg-white border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Expenses</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_cost) }}</div>
            <div class="text-xs text-gray-400 mt-1">Inventory + Labor</div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-white border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-1">
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
    <div class="space-y-3 mb-4">
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

        <div class="grid grid-cols-1 gap-3">
            {{-- Payment Recording --}}
            <div class="bg-white border-2 border-green-200 shadow-lg">
                <div class="px-6 py-4 border-b border-green-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        <h2 class="text-sm font-bold text-green-700 uppercase tracking-wider">Record Payment</h2>
                    </div>
                </div>
                <div class="p-5">
                    {{-- Payment Breakdown --}}
                    <div class="bg-gradient-to-br from-gray-50 to-slate-50 space-y-2 mb-4 pb-4 border-b-2 border-green-100 rounded-lg p-3">
                        <div class="flex items-center justify-between text-sm font-medium">
                            <span class="text-gray-700">Subtotal</span>
                            <span class="text-gray-900 font-semibold">₱{{ number_format($order->total_gross) }}</span>
                        </div>
                        
                        @if($order->total_gross - $order->total_amount > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700">Discount ({{ round((($order->total_gross - $order->total_amount) / $order->total_gross) * 100) }}%)</span>
                                <span class="text-red-600 font-semibold">- ₱{{ number_format($order->total_gross - $order->total_amount) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm font-bold pt-3 border-t-2 border-green-200">
                            <span class="text-gray-900">Total Due</span>
                            <span class="text-green-700 text-lg">₱{{ number_format($order->total_amount) }}</span>
                        </div>
                        
                        @if($order->payments->count() > 0)
                            <div class="pt-4 border-t-2 border-green-100">
                                <p class="text-xs font-bold text-gray-700 uppercase mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Payment History
                                </p>
                                <div class="space-y-2 bg-white rounded p-3 border border-green-100">
                                    @foreach($order->payments as $payment)
                                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-green-700">₱{{ number_format($payment->amount) }}</span>
                                                <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $payment->created_at->format('M d') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center justify-between text-sm font-bold pt-3 mt-3 border-t-2 border-green-100">
                                    <span class="text-gray-800">Total Paid</span>
                                    <span class="text-green-600 text-lg">₱{{ number_format($totalPaid) }}</span>
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm font-bold pt-3 mt-3 border-t-2 border-red-200 bg-red-50 p-3 rounded">
                            <span class="text-gray-900">Remaining Balance</span>
                            <span class="text-red-600 text-xl">₱{{ number_format($remainingBalance) }}</span>
                        </div>
                    </div>

                    @if($remainingBalance > 0)
                        @if(!$showPaymentForm)
                            <div class="mt-6">
                                <button wire:click="togglePaymentForm" class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-bold uppercase tracking-wide transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.5 1.5H5.75C4.232 1.5 3 2.732 3 4.25v11.5C3 17.268 4.232 18.5 5.75 18.5h8.5c1.518 0 2.75-1.232 2.75-2.75V8.5m-8-5v5m0 0h5M10.5 3.5v5m4.25 6.75h-8.5"/>
                                    </svg>
                                    Add Payment
                                </button>
                            </div>
                        @else
                            <div class="mt-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-6 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Payment Amount (₱)</label>
                                    <input type="number" wire:model="paymentAmount" placeholder="0" min="0" max="{{ $remainingBalance }}" class="w-full px-4 py-3 border-2 border-green-200 rounded-lg text-sm focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 font-semibold">
                                    @error('paymentAmount') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    <p class="text-xs text-green-700 font-medium mt-2">Maximum: ₱{{ number_format($remainingBalance) }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Payment Method</label>
                                    <select wire:model="paymentMethod" class="w-full px-4 py-3 border-2 border-green-200 rounded-lg text-sm focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200">
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit_card">Credit Card</option>
                                    </select>
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button wire:click="addPayment" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-bold uppercase tracking-wide transition rounded-lg shadow-md hover:shadow-lg">
                                        Confirm Payment
                                    </button>
                                    <button wire:click="togglePaymentForm" class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-bold uppercase tracking-wide transition rounded-lg">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4 flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-center text-sm font-bold text-green-700 uppercase">Payment Complete</p>
                                <p class="text-xs text-green-600">This order has been fully paid</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer & Vehicle Details --}}
            <div class="bg-white border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Customer & Vehicle Details</h2>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-3 gap-3 text-sm">
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
        <div class="lg:col-span-2 space-y-4">
            
            {{-- Products Consumed --}}
            @if($order->orderItems->where('product_id', '!=', null)->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-blue-50 border-blue-200">
                        <h2 class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Products Consumed</h2>
                    </div>
                    <div class="p-5">
                        <div class="space-y-4">
                           @foreach($order->orderItems->where('product_id', '!=', null) as $item)
                                <div class="border border-blue-200 rounded-lg p-4 bg-blue-50 shadow-sm">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-lg">{{ $item->product->name ?? 'Unknown Product' }}</h3>
                                            <p class="text-xs text-gray-500 mt-1">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900 text-xl">₱{{ number_format($item->total_price) }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Stock Deducted</p>
                                            <p class="text-sm text-gray-900"><span class="font-semibold">{{ $item->quantity }}</span> unit{{ $item->quantity > 1 ? 's' : '' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Unit Price</p>
                                            <p class="text-sm text-gray-900 font-semibold">₱{{ number_format($item->unit_price) }}</p>
                                        </div>
                                        @if(auth()->user()->role === 'admin')
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Base Price (Cost)</p>
                                            <p class="text-sm text-gray-900 font-semibold text-red-600">₱{{ number_format($item->product->buy_price ?? 0) }}</p>
                                        </div>
                                        @endif
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Sell Price</p>
                                            <p class="text-sm text-gray-900 font-semibold text-blue-600">₱{{ number_format($item->product->sell_price ?? 0) }}</p>
                                        </div>
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
                    <div class="px-6 py-3 border-b border-gray-200 bg-orange-50 border-orange-200">
                        <h2 class="text-sm font-semibold text-orange-700 uppercase tracking-wide">Services Performed</h2>
                    </div>
                    <div class="p-5">
                        <div class="space-y-4">
                            @foreach($order->orderItems->where('service_id', '!=', null) as $item)
                                <div class="border border-orange-200 rounded-lg p-4 bg-orange-50 shadow-sm">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-lg">{{ $item->service->name ?? 'Unknown Service' }}</h3>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900 text-xl">₱{{ number_format($item->total_price) }}</p>
                                        </div>
                                    </div>
                                    
                                    @php
                                        $crewMembers = $order->serviceAssignments->where('service_id', $item->service_id);
                                    @endphp
                                    @if($crewMembers->count() > 0)
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">Assigned Crew</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($crewMembers as $assignment)
                                                    <span class="inline-flex items-center px-3 py-1 text-xs bg-white text-gray-700 border border-orange-200 rounded-full font-medium">
                                                        {{ $assignment->employee->name ?? 'Unknown' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
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
                    <div class="p-5">
                        <div class="space-y-4">
                            @foreach($order->orderItems->where('upholstery_id', '!=', null) as $item)
                                @php
                                    $upholstery = $item->upholstery;
                                @endphp
                                <div class="border border-purple-200 rounded-lg p-4 bg-purple-50 shadow-sm">
                                    <div class="flex items-start justify-between mb-3">
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

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Installation Date</p>
                                            <p class="text-sm text-gray-900">{{ $upholstery->installation_date ? $upholstery->installation_date->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Services Included</p>
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
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- VIP Packages --}}
            @if($order->orderItems->where('vip_id', '!=', null)->count() > 0)
                <div class="bg-white border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200 bg-indigo-50 border-indigo-200">
                        <h2 class="text-sm font-semibold text-indigo-700 uppercase tracking-wide">VIP Packages</h2>
                    </div>
                    <div class="p-5">
                        <div class="space-y-4">
                            @foreach($order->orderItems->where('vip_id', '!=', null) as $item)
                                @php
                                    $vip = $item->vip;
                                @endphp
                                <div class="border border-indigo-200 rounded-lg p-4 bg-indigo-50 shadow-sm">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-lg">VIP Package</h3>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900 text-xl">₱{{ number_format($item->total_price) }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                        {{-- Stepboard --}}
                                        <div class="bg-white p-2 rounded border border-indigo-100">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Stepboard</p>
                                            <div class="space-y-1">
                                                <p class="text-sm text-gray-900"><span class="font-medium">{{ $vip->stepboard_pcs }}</span> pcs</p>
                                                <p class="text-sm font-semibold text-indigo-600">₱{{ number_format($vip->stepboard_amount) }}</p>
                                            </div>
                                        </div>

                                        {{-- Engine Bay --}}
                                        <div class="bg-white p-2 rounded border border-indigo-100">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Engine Bay</p>
                                            <div class="space-y-1">
                                                <p class="text-sm text-gray-900"><span class="font-medium">{{ $vip->engine_bay_pcs }}</span> pcs</p>
                                                <p class="text-sm font-semibold text-indigo-600">₱{{ number_format($vip->engine_bay_amount) }}</p>
                                            </div>
                                        </div>

                                        {{-- Console Box --}}
                                        <div class="bg-white p-2 rounded border border-indigo-100">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Console Box</p>
                                            <div class="space-y-1">
                                                <p class="text-sm text-gray-900"><span class="font-medium">{{ $vip->console_box_pcs }}</span> pcs</p>
                                                <p class="text-sm font-semibold text-indigo-600">₱{{ number_format($vip->console_box_amount) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Total Breakdown --}}
                                    <div class="bg-white p-2 rounded border border-indigo-200 mb-3">
                                        <p class="text-xs font-medium text-gray-600 uppercase mb-1">Total Breakdown</p>
                                        <div class="space-y-1 text-sm">
                                            @if($vip->stepboard_amount > 0)
                                                <div class="flex justify-between text-gray-700">
                                                    <span>Stepboard:</span>
                                                    <span>₱{{ number_format($vip->stepboard_amount) }}</span>
                                                </div>
                                            @endif
                                            @if($vip->engine_bay_amount > 0)
                                                <div class="flex justify-between text-gray-700">
                                                    <span>Engine Bay:</span>
                                                    <span>₱{{ number_format($vip->engine_bay_amount) }}</span>
                                                </div>
                                            @endif
                                            @if($vip->console_box_amount > 0)
                                                <div class="flex justify-between text-gray-700">
                                                    <span>Console Box:</span>
                                                    <span>₱{{ number_format($vip->console_box_amount) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between font-semibold text-indigo-700 pt-2 border-t border-indigo-100">
                                                <span>Total Amount:</span>
                                                <span>₱{{ number_format($vip->total_amount) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($vip->description)
                                        <div class="mb-4">
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-1">Description</p>
                                            <p class="text-sm text-gray-700 bg-white p-3 rounded border border-gray-200">{{ $vip->description }}</p>
                                        </div>
                                    @endif

                                    @if($vip->photo)
                                        <div>
                                            <p class="text-xs font-medium text-gray-600 uppercase mb-2">Photo</p>
                                            <img src="{{ asset('storage/' . $vip->photo) }}" alt="VIP Package Photo" class="h-32 w-auto rounded border border-gray-200 shadow-sm">
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
                    <div class="p-5">
                        <div class="space-y-3">
                            @foreach($order->expenses as $expense)
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $expense->description }}</p>
                                        <div class="flex gap-3 mt-1 text-sm">
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
