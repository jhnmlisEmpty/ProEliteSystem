<div class="max-w-7xl mx-auto">
    {{-- Print-Only Invoice Header --}}
    <div class="print-only mb-4">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('logo.png') }}" alt="Pro Elite Logo" class="w-16 h-16 rounded-full border border-gray-400 object-cover">
                <div>
                    <p class="text-sm font-bold text-gray-900">PRO ELITE CAR UPHOLSTERY</p>
                    <p class="text-xs text-gray-700">72 QUEEN OF PEACE ROAD, LOURDES SUBDIVISION EXTENSION BAGUIO CITY</p>
                    <p class="text-xs text-gray-700">CONTACT #: 09266530192</p>
                </div>
            </div>
            <div class="text-xs text-gray-700 text-right">
                <p>Created {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        <div class="mt-3 text-xs font-semibold text-gray-900">Order #: ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</div>
    </div>
    {{-- Header --}}
    <div class="mb-6 print-hide">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="flex items-center gap-3 ">
                    <h1 class="text-3xl font-bold text-gray-900">Order #ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</h1>
                    <select wire:change="changeStatus($event.target.value)" class="px-3 py-1 rounded border border-gray-300 text-xs font-medium uppercase text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <select wire:change="changePaymentStatus($event.target.value)" class="px-3 py-1 rounded border border-gray-300 text-xs font-medium uppercase text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($paymentStatusOptions as $paymentStatus)
                            <option value="{{ $paymentStatus }}" {{ $order->payment_status === $paymentStatus ? 'selected' : '' }}>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 print-hide">
        {{-- Total Revenue (Gross) --}}
        <div class="bg-white border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_gross) }}</div>
            <div class="text-xs text-gray-400 mt-1">Includes billable fees</div>
        </div>

        @if(auth()->user()->role === 'admin')
        {{-- Total Expenses --}}
        <div class="bg-white border border-gray-200 p-4 ">
            <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Expenses</div>
            <div class="text-2xl font-semibold text-gray-900">₱{{ number_format($order->total_cost) }}</div>
            <div class="text-xs text-gray-400 mt-1">Inventory</div>
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

    {{-- Remaining Balance & Customer Info Section --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-0">
            {{-- Remaining Balance & Add Payment --}}
            <div class="px-6 py-6 border-r border-gray-200 lg:col-span-1  print-hide">
                <div class="mb-3">
                    <h2 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-2">Remaining Balance</h2>
                    <div class="text-3xl font-bold text-gray-900">₱{{ number_format($remainingBalance) }}</div>
                    <p class="text-xs text-gray-500 mt-1">Includes billable fees</p>
                </div>
                
                @if($remainingBalance > 0)
                    @if(!$showPaymentForm)
                        <button wire:click="togglePaymentForm" class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase tracking-wide transition rounded">
                            + Add Payment
                        </button>
                    @else
                        <div class="bg-green-50 border-2 border-green-200 rounded p-3 space-y-2 mt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Amount (₱)</label>
                                <input type="number" wire:model="paymentAmount" placeholder="0" min="0" max="{{ $remainingBalance }}" class="w-full px-3 py-2 border-2 border-green-200 rounded text-sm focus:outline-none focus:border-green-500 font-semibold">
                                @error('paymentAmount') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Method</label>
                                <select wire:model="paymentMethod" class="w-full px-3 py-2 border-2 border-green-200 rounded text-sm focus:outline-none focus:border-green-500">
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                </select>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button wire:click="addPayment" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase transition rounded">
                                    Confirm
                                </button>
                                <button wire:click="togglePaymentForm" class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold uppercase transition rounded">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-green-50 border-2 border-green-200 rounded p-3 flex items-center gap-2 mt-4">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-xs font-bold text-green-700 uppercase">Fully Paid</p>
                    </div>
                @endif
            </div>

            {{-- Customer & Vehicle Details --}}
            <div class="px-6 py-6 lg:col-span-2">
                <h2 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4">Customer & Vehicle Details</h2>
                <div class="space-y-4">
                    <div class="flex gap-6">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Customer Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Phone</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->customer?->phone ?? 'N/A' }}</p>
                        </div>
                        @php
                            $upholsteryItem = $order->orderItems->where('upholstery_id', '!=', null)->first();
                        @endphp
                        @if($upholsteryItem?->upholstery?->unit_year_model)
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Vehicle Model</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $upholsteryItem->upholstery->unit_type ?? 'N/A' }} - {{ $upholsteryItem->upholstery->unit_year_model }}</p>
                            </div>
                        @endif
                        @if($order->customer?->address)
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Address</p>
                                <p class="text-sm text-gray-900">{{ $order->customer->address }}</p>
                            </div>
                        @endif
                    </div>
                   
                </div>
            </div>
        </div>
    </div>

    {{-- Consolidated Order Items Table & Payment Records --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Order Items Table (2/3 width) --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b-2 border-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Date</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Items</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-800 uppercase tracking-wide">Unit Price</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-800 uppercase tracking-wide">Sub Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Products Consumed --}}
                    @foreach($order->orderItems->where('product_id', '!=', null) as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $order->created_at->format('M d') }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                <p class="font-medium">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                @if($item->product->sku)
                                    <p class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</p>
                                @endif
                                @if($item->quantity > 1)
                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->unit_price) }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->total_price) }}</td>
                        </tr>
                    @endforeach

                    {{-- Services Performed --}}
                    @foreach($order->orderItems->where('service_id', '!=', null) as $item)
                        @php
                            $crewMembers = $order->serviceAssignments->where('service_id', $item->service_id);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $order->created_at->format('M d') }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                <p class="font-medium">{{ $item->service->name ?? 'Unknown Service' }}</p>
                                @if($crewMembers->count() > 0)
                                    <p class="text-xs text-gray-500">Assigned Employee: {{ $crewMembers->pluck('employee.name')->join(', ') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->unit_price) }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->total_price) }}</td>
                        </tr>
                    @endforeach

                    {{-- Upholstery Services --}}
                    @foreach($order->orderItems->where('upholstery_id', '!=', null) as $item)
                        @php
                            $upholstery = $item->upholstery;
                            $crewMembers = $order->upholsteryAssignments->where('upholstery_id', $upholstery->id);
                            $serviceLabels = [
                                'seat_cover' => 'Seat Cover',
                                'ceiling' => 'Ceiling',
                                'sidings' => 'Sidings',
                                'rubber_mattings' => 'Rubber Mattings',
                                'front_mattings' => 'Front Mattings',
                                'headrest' => 'Headrest',
                            ];
                            $selectedServices = [];
                            if (is_array($upholstery->services)) {
                                foreach ($upholstery->services as $key => $value) {
                                    if ($value && isset($serviceLabels[$key])) {
                                        $selectedServices[] = $serviceLabels[$key];
                                    }
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $order->created_at->format('M d') }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col gap-2">
                                        @if($upholstery->photos && is_array($upholstery->photos) && count($upholstery->photos) > 0)
                                            @foreach($upholstery->photos as $photo)
                                                <img src="{{ asset('storage/' . $photo) }}" alt="Upholstery Photo" class="h-24 w-auto rounded border border-gray-300 shadow-sm">
                                            @endforeach
                                        @elseif($upholstery->photo_path)
                                            <img src="{{ asset('storage/' . $upholstery->photo_path) }}" alt="Upholstery Photo" class="h-24 w-auto rounded border border-gray-300 shadow-sm">
                                        @endif
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium text-red-700">Upholstery Services</p>
                                        <p class="text-xs text-gray-500">{{ $upholstery->unit_type ?? 'N/A' }} - {{ $upholstery->unit_year_model }}</p>
                                        <p class="text-xs text-gray-500">Installation: {{ date('M d, Y', strtotime($upholstery->installation_date)) }}</p>
                                        <div class="text-xs space-y-1">
                                            @if($upholstery->seat_cover_description)
                                                <p class="italic"><strong>Seat Description:</strong> {{ $upholstery->seat_cover_description }}</p>
                                            @endif
                                            @if($upholstery->ceiling_description)
                                                <p class="italic"><strong>Ceiling Description:</strong> {{ $upholstery->ceiling_description }}</p>
                                            @endif
                                            @if($upholstery->sidings_description)
                                                <p class="italic"><strong>Sidings Description:</strong> {{ $upholstery->sidings_description }}</p>
                                            @endif
                                            @if($upholstery->rubber_mattings_description)
                                                <p class="italic"><strong>Rubber Description:</strong> {{ $upholstery->rubber_mattings_description }}</p>
                                            @endif
                                            @if($upholstery->front_mattings_description)
                                                <p class="italic"><strong>Front Description:</strong> {{ $upholstery->front_mattings_description }}</p>
                                            @endif
                                            @if($upholstery->headrest_description)
                                                <p class="italic"><strong>Headrest Description:</strong> {{ $upholstery->headrest_description }}</p>
                                            @endif
                                        </div>
                                        <div class="text-xs">
                                            <span class="font-semibold">Services:</span>
                                            @if(count($selectedServices) > 0)
                                                @foreach($selectedServices as $service)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800 mr-1 mt-1">{{ $service }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </div>
                                        @if($crewMembers->count() > 0)
                                            <p class="text-xs text-gray-600 mt-2">
                                                <span class="font-semibold">Assigned Crew:</span>
                                                @foreach($crewMembers as $crew)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-800 mr-1 mt-1">{{ $crew->employee->name }}</span>
                                                @endforeach
                                            </p>
                                        @endif
                                        @if($upholstery->description)
                                            <p class="italic text-xs text-gray-700 mt-2">
                                                <span class="font-semibold">Description:</span>
                                                {{ $upholstery->description }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                <div class="text-xs space-y-1">
                                    @if(($upholstery->seat_cover_amount ?? 0) > 0)
                                        <p>Seat: ₱{{ number_format($upholstery->seat_cover_amount) }}</p>
                                    @endif
                                    @if(($upholstery->ceiling_amount ?? 0) > 0)
                                        <p>Ceiling: ₱{{ number_format($upholstery->ceiling_amount) }}</p>
                                    @endif
                                    @if(($upholstery->sidings_amount ?? 0) > 0)
                                        <p>Sidings: ₱{{ number_format($upholstery->sidings_amount) }}</p>
                                    @endif
                                    @if(($upholstery->rubber_mattings_amount ?? 0) > 0)
                                        <p>Rubber: ₱{{ number_format($upholstery->rubber_mattings_amount) }}</p>
                                    @endif
                                    @if(($upholstery->front_mattings_amount ?? 0) > 0)
                                        <p>Front: ₱{{ number_format($upholstery->front_mattings_amount) }}</p>
                                    @endif
                                    @if(($upholstery->headrest_amount ?? 0) > 0)
                                        <p>Headrest: ₱{{ number_format($upholstery->headrest_amount) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->total_price) }}</td>
                        </tr>
                    @endforeach

                    {{-- VIP Packages --}}
                    @foreach($order->orderItems->where('vip_id', '!=', null) as $item)
                        @php
                            $vip = $item->vip;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $order->created_at->format('M d') }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col gap-2">
                                        @if($vip->photos && is_array($vip->photos) && count($vip->photos) > 0)
                                            @foreach($vip->photos as $photo)
                                                <img src="{{ asset('storage/' . $photo) }}" alt="VIP Package Photo" class="h-24 w-auto rounded border border-gray-300 shadow-sm">
                                            @endforeach
                                        @elseif($vip->photo)
                                            <img src="{{ asset('storage/' . $vip->photo) }}" alt="VIP Package Photo" class="h-24 w-auto rounded border border-gray-300 shadow-sm">
                                        @endif
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium">VIP Package</p>
                                        <div class="text-xs space-y-1">
                                            @if(($vip->stepboard_amount ?? 0) > 0)
                                                <p>Stepboard: {{ $vip->stepboard_pcs }} pcs @ ₱{{ number_format($vip->stepboard_unit_price ?? 0) }}</p>
                                            @endif
                                            @if(($vip->engine_bay_amount ?? 0) > 0)
                                                <p>Engine: {{ $vip->engine_bay_pcs }} pcs @ ₱{{ number_format($vip->engine_bay_unit_price ?? 0) }}</p>
                                            @endif
                                            @if(($vip->console_box_amount ?? 0) > 0)
                                                <p>Console: {{ $vip->console_box_pcs }} pcs @ ₱{{ number_format($vip->console_box_unit_price ?? 0) }}</p>
                                            @endif
                                            @if(($vip->thai_ceiling_amount ?? 0) > 0)
                                                <p>Thai Ceiling: {{ $vip->thai_ceiling_pcs }} pcs @ ₱{{ number_format($vip->thai_ceiling_unit_price ?? 0) }}</p>
                                            @endif
                                        </div>
                                        @if($vip->description)
                                            <p class="text-xs text-gray-500">{{ $vip->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                <div class="text-xs space-y-1">
                                    @if(($vip->stepboard_amount ?? 0) > 0)
                                        <p>
                                            Stepboard: ₱{{ number_format($vip->stepboard_amount) }}
                                        </p>
                                    @endif
                                    @if(($vip->engine_bay_amount ?? 0) > 0)
                                        <p>
                                            Engine: ₱{{ number_format($vip->engine_bay_amount) }}
                                        </p>
                                    @endif
                                    @if(($vip->console_box_amount ?? 0) > 0)
                                        <p>
                                            Console: ₱{{ number_format($vip->console_box_amount) }}
                                        </p>
                                    @endif
                                    @if(($vip->thai_ceiling_amount ?? 0) > 0)
                                        <p>
                                            Thai Ceiling: ₱{{ number_format($vip->thai_ceiling_amount) }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($item->total_price) }}</td>
                        </tr>
                    @endforeach

                    {{-- Misc. Expenses --}}
                    @foreach($order->expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $order->created_at->format('M d') }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                <p class="font-medium">{{ $expense->description }}</p>
                                <p class="text-xs text-gray-500">
                                    @if($expense->is_billable)
                                        <span class="inline-flex items-center px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Billable</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Internal</span>
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($expense->my_cost) }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">₱{{ number_format($expense->charge_client) }}</td>
                        </tr>
                    @endforeach

                    {{-- TOTALS ROW --}}
                    @php
                        $totalPaid = $order->payments->sum('amount');
                        $balance = $order->total_amount - $totalPaid;
                    @endphp
                    <tr class="bg-gray-100 border-t-2 border-gray-400 font-bold">
                        <td colspan="2" class="px-6 py-4 text-right text-gray-900 uppercase"></td>
                        <td colspan="2" class="px-6 py-4 text-gray-900">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="uppercase">Sub Total:</span>
                                    <span>₱{{ number_format($order->total_gross) }}</span>
                                </div>
                                @if($order->discounted_amount > 0)
                                    <div class="flex justify-between text-orange-600">
                                        <span class="uppercase">Discount:</span>
                                        <span>-₱{{ number_format($order->discounted_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span class="uppercase">Sub Total after Discount:</span>
                                        <span>₱{{ number_format($order->total_amount) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-blue-600">
                                    <span class="uppercase">Amount Paid:</span>
                                    <span>₱{{ number_format($totalPaid) }}</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span class="uppercase">Balance:</span>
                                    <span>₱{{ number_format($balance) }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Payment Records (1/3 width) --}}
        <div class="bg-white border border-gray-200 print-hide">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h2 class="text-sm font-bold text-blue-700 uppercase tracking-wide">Payment Records</h2>
            </div>
            <div class="p-6">
                @if($order->payments->count() > 0)
                    <div class="space-y-4">
                        @foreach($order->payments as $payment)
                            <div class="border border-gray-200 rounded p-3 bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Payment Method</p>
                                        <p class="text-sm font-bold text-gray-900 uppercase">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <p class="text-lg font-bold text-green-600">₱{{ number_format($payment->amount) }}</p>
                                </div>
                                @if($payment->reference)
                                    <p class="text-xs text-gray-600 bg-white p-2 rounded border border-gray-100">Ref: {{ $payment->reference }}</p>
                                @endif
                            </div>
                        @endforeach
                        
                        <div class="border-t pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-700 uppercase">Total Paid</span>
                                <span class="text-xl font-bold text-blue-600">₱{{ number_format($totalPaid) }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-center text-gray-500 text-sm py-6">No payments recorded yet</p>
                @endif
            </div>
        </div>
    </div>

    <div class="print-only grid grid-cols-1 gap-8 mt-12">
        <div class="grid grid-cols-2 gap-16">
            <div>
                <p class="text-xs font-bold text-gray-700 uppercase">Prepared By:</p>
                <div class="mt-12 border-t border-gray-400"></div>
                <p class="text-xs text-gray-700 text-center mt-2 uppercase">Authorized Signature</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-700 uppercase">Checked By:</p>
                <div class="mt-12 border-t border-gray-400"></div>
                <p class="text-xs text-gray-700 text-center mt-2 uppercase">Customer Signature</p>
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav,
            .sticky,
            .print-hide {
                display: none !important;
            }

            body {
                background: #fff;
            }

            main {
                padding-top: 0 !important;
            }

            .shadow,
            .shadow-md,
            .shadow-lg,
            .shadow-xl {
                box-shadow: none !important;
            }

            section,
            table,
            tr {
                page-break-inside: avoid;
            }
            .max-w-7xl { max-width: 100% !important; }
        }
        .print-only { display: none; }
        @media print { .print-only { display: block !important; } }
    </style>
</div>


