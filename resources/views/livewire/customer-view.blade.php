<div>
    {{-- Header --}}
    <x-page-header title="Customer Details" :subtitle="'View profile and orders for ' . $customer->name">
        <x-slot name="actions">
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </a>
        </x-slot>
    </x-page-header>

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

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Remaining Balance & Customer Details Section --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Remaining Balance Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-600 mb-4">REMAINING BALANCE</h3>
            <div class="flex items-baseline justify-between">
                <p class="text-4xl font-bold text-gray-900">₱{{ number_format($remainingBalance, 0) }}</p>
            </div>
        </div>

        {{-- Customer & Vehicle Details --}}
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-600 mb-4">CUSTOMER & VEHICLE DETAILS</h3>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Customer Name</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $customer->name }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Phone</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Vehicle Type</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $customer->vehicle_type ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Location</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $customer->address ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Plate Number</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $customer->plate_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Total Orders</label>
                    <p class="text-lg font-medium text-gray-900 mt-1">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalOrders }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Spent</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">₱{{ number_format($totalSpent, 0) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Balance Due</p>
                    <p class="text-3xl font-bold {{ $remainingBalance > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">₱{{ number_format(abs($remainingBalance), 0) }}</p>
                </div>
                <div class="p-3 {{ $remainingBalance > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg">
                    <svg class="w-8 h-8 {{ $remainingBalance > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders & Order Items Table with Payment Records --}}
    <div class="space-y-6">
        @if($orders->count() > 0)
            @foreach($orders as $order)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Order Items (Left) --}}
                    <div class="lg:col-span-2 bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Order <span class="text-blue-600">ORD-{{ $order->id }}</span>
                                </h3>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                    @switch($order->status)
                                        @case('pending')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('in_progress')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('completed')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('cancelled')
                                            bg-red-100 text-red-800
                                            @break
                                    @endswitch
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                    @switch($order->payment_status)
                                        @case('unpaid')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('partial')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('paid')
                                            bg-green-100 text-green-800
                                            @break
                                    @endswitch
                                ">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($order->orderItems as $item)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div>
                                                    @if($item->product)
                                                        <p class="font-medium">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                                        <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                                    @elseif($item->service)
                                                        <p class="font-medium">{{ $item->service->name }}</p>
                                                        @if($item->service->assigned_employee)
                                                            <p class="text-xs text-gray-500">Assigned Employee: {{ $item->service->assigned_employee }}</p>
                                                        @endif
                                                    @elseif($item->upholstery_id)
                                                        <p class="font-medium">Upholstery Services</p>
                                                        <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                                    @elseif($item->vip_id)
                                                        <p class="font-medium">VIP Package</p>
                                                        <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                ₱{{ number_format($item->unit_price, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm font-semibold text-blue-600">
                                                ₱{{ number_format($item->total_price, 0) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Order Summary --}}
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-end gap-4">
                                    <span class="font-medium text-gray-700">SUB TOTAL:</span>
                                    <span class="font-semibold text-gray-900">₱{{ number_format($order->total_amount, 0) }}</span>
                                </div>
                                @php
                                    $orderPayments = \App\Models\Payment::where('order_id', $order->id)->sum('amount');
                                    $orderBalance = $order->total_amount - $orderPayments;
                                @endphp
                                <div class="flex justify-end gap-4">
                                    <span class="font-medium text-gray-700">AMOUNT PAID:</span>
                                    <span class="font-semibold text-blue-600">₱{{ number_format($orderPayments, 0) }}</span>
                                </div>
                                <div class="flex justify-end gap-4 pt-2 border-t border-gray-300">
                                    <span class="font-medium text-gray-700">BALANCE:</span>
                                    <span class="font-semibold {{ $orderBalance > 0 ? 'text-red-600' : 'text-green-600' }}">₱{{ number_format(abs($orderBalance), 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Records (Right) --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Payment Records</h3>
                            </div>

                            @php
                                $orderPaymentRecords = \App\Models\Payment::where('order_id', $order->id)->orderBy('created_at', 'desc')->get();
                            @endphp

                            @if($orderPaymentRecords->count() > 0)
                                <div class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
                                    @foreach($orderPaymentRecords as $payment)
                                        <div class="px-6 py-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500 uppercase">Payment Method</span>
                                                <span class="text-sm font-semibold text-green-600">₱{{ number_format($payment->amount, 0) }}</span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900 mb-1">{{ ucfirst($payment->method ?? 'N/A') }}</p>
                                            <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y') }}</p>
                                        </div>
                                    @endforeach

                                    {{-- Total Paid --}}
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-semibold text-gray-700">TOTAL PAID</span>
                                            <span class="text-xl font-bold text-blue-600">₱{{ number_format($orderPaymentRecords->sum('amount'), 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="px-6 py-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No payments</h3>
                                    <p class="mt-1 text-xs text-gray-500">No payment records yet.</p>
                                </div>
                            @endif

                            {{-- Add Payment Button --}}
                            @if($orderBalance > 0)
                                @if(!($showPaymentForm[$order->id] ?? false))
                                    <div class="px-6 py-4 border-t border-gray-200">
                                        <button 
                                            wire:click="togglePaymentForm({{ $order->id }})"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            ADD PAYMENT
                                        </button>
                                    </div>
                                @else
                                    <div class="px-6 py-4 border-t border-gray-200">
                                        <div class="bg-green-50 border-2 border-green-200 rounded p-3 space-y-2">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Amount (₱)</label>
                                                <input 
                                                    type="number" 
                                                    wire:model="paymentAmount" 
                                                    placeholder="0" 
                                                    min="0" 
                                                    max="{{ $orderBalance }}" 
                                                    class="w-full px-3 py-2 border-2 border-green-200 rounded text-sm focus:outline-none focus:border-green-500 font-semibold"
                                                >
                                                @error('paymentAmount') 
                                                    <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> 
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Method</label>
                                                <select 
                                                    wire:model="paymentMethod" 
                                                    class="w-full px-3 py-2 border-2 border-green-200 rounded text-sm focus:outline-none focus:border-green-500"
                                                >
                                                    <option value="cash">Cash</option>
                                                    <option value="gcash">GCash</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="credit_card">Credit Card</option>
                                                </select>
                                            </div>

                                            <div class="flex gap-2 pt-2">
                                                <button 
                                                    wire:click="addPayment({{ $order->id }})"
                                                    class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase transition rounded"
                                                >
                                                    Confirm
                                                </button>
                                                <button 
                                                    wire:click="togglePaymentForm({{ $order->id }})"
                                                    class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold uppercase transition rounded"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Pagination --}}
            <div class="bg-white rounded-lg shadow px-6 py-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders</h3>
                <p class="mt-1 text-sm text-gray-500">This customer hasn't placed any orders yet.</p>
            </div>
        @endif
    </div>
</div>
