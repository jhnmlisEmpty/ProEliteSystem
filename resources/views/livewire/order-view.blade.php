<div>
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">← Back to Orders</a>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-gray-600">Created on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <div class="inline-flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($order->payment_status === 'partial' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
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

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Order Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Customer Information --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Customer Information</h2>
                    @if($order->customer)
                        <a href="{{ route('customers.edit', $order->customer->id) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View Profile</a>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Customer Name</p>
                        <p class="font-medium text-gray-900">{{ $order->customer_name }}</p>
                    </div>
                    @if($order->customer)
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium text-gray-900">{{ $order->customer->phone }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium text-gray-900">{{ $order->customer->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Vehicle Information --}}
            @if($order->vehicle_type || $order->plate_number)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($order->vehicle_type)
                            <div>
                                <p class="text-sm text-gray-600">Vehicle Type</p>
                                <p class="font-medium text-gray-900">{{ $order->vehicle_type }}</p>
                            </div>
                        @endif
                        @if($order->plate_number)
                            <div>
                                <p class="text-sm text-gray-600">Plate Number</p>
                                <p class="font-medium text-gray-900">{{ $order->plate_number }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Order Items --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                @if($order->orderItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 font-medium text-gray-700">Item</th>
                                    <th class="text-right py-2 px-2 font-medium text-gray-700">Type</th>
                                    <th class="text-right py-2 px-2 font-medium text-gray-700">Qty</th>
                                    <th class="text-right py-2 px-2 font-medium text-gray-700">Unit Price</th>
                                    <th class="text-right py-2 px-2 font-medium text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                                            </div>
                                        </td>
                                        <td class="text-right py-3 px-2">
                                            @if($item->item_type === 'product')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Product</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">Service</span>
                                            @endif
                                        </td>
                                        <td class="text-right py-3 px-2 font-medium text-gray-900">{{ $item->quantity }}</td>
                                        <td class="text-right py-3 px-2 text-gray-600">₱{{ number_format($item->unit_price) }}</td>
                                        <td class="text-right py-3 px-2 font-bold text-gray-900">₱{{ number_format($item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-gray-500">No items in this order</p>
                @endif
            </div>

            {{-- Job Order (if services were ordered) --}}
            @if($order->jobOrder)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Order</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $order->jobOrder->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->jobOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($order->jobOrder->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="font-medium text-gray-900">
                                @if($order->jobOrder->start_date && $order->jobOrder->end_date)
                                    {{ $order->jobOrder->duration_in_days }} day(s)
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        @if($order->jobOrder->start_date)
                            <div>
                                <p class="text-sm text-gray-600">Start Date</p>
                                <p class="font-medium text-gray-900">{{ $order->jobOrder->start_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if($order->jobOrder->end_date)
                            <div>
                                <p class="text-sm text-gray-600">End Date</p>
                                <p class="font-medium text-gray-900">{{ $order->jobOrder->end_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                    @if($order->jobOrder->notes)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Notes</p>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded">{{ $order->jobOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Payment History --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h2>
                @if($order->payments->count() > 0)
                    <div class="space-y-3">
                        @foreach($order->payments as $payment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div>
                                    <p class="font-medium text-gray-900">₱{{ number_format($payment->amount) }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ ucfirst($payment->method) }} • {{ $payment->paid_at->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-gray-500">No payments recorded yet</p>
                @endif
            </div>
        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="space-y-6">
            {{-- Order Summary --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium text-gray-900">₱{{ number_format($order->total_amount) }}</span>
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Paid</span>
                            <span class="font-medium text-green-600">₱{{ number_format($order->total_paid) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900">Remaining Balance</span>
                            <span class="font-bold text-lg {{ $order->remaining_balance > 0 ? 'text-red-600' : 'text-green-600' }}">₱{{ number_format($order->remaining_balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Management --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h2>
                <div class="space-y-2">
                    <button wire:click="updateOrderStatus('pending')" class="w-full text-left px-4 py-2 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-400 transition {{ $order->status === 'pending' ? 'bg-blue-50 border-blue-400 font-medium text-blue-600' : 'text-gray-700' }}">
                        Pending
                    </button>
                    <button wire:click="updateOrderStatus('in_progress')" class="w-full text-left px-4 py-2 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-400 transition {{ $order->status === 'in_progress' ? 'bg-blue-50 border-blue-400 font-medium text-blue-600' : 'text-gray-700' }}">
                        In Progress
                    </button>
                    <button wire:click="updateOrderStatus('completed')" class="w-full text-left px-4 py-2 rounded-lg border border-gray-200 hover:bg-green-50 hover:border-green-400 transition {{ $order->status === 'completed' ? 'bg-green-50 border-green-400 font-medium text-green-600' : 'text-gray-700' }}">
                        Completed
                    </button>
                    <button wire:click="updateOrderStatus('cancelled')" class="w-full text-left px-4 py-2 rounded-lg border border-gray-200 hover:bg-red-50 hover:border-red-400 transition {{ $order->status === 'cancelled' ? 'bg-red-50 border-red-400 font-medium text-red-600' : 'text-gray-700' }}">
                        Cancelled
                    </button>
                </div>
            </div>

            {{-- Payment Section --}}
            @if($order->remaining_balance > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Payment</h2>
                    
                    @if($showPaymentForm)
                        <form wire:submit.prevent="addPayment" class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <input type="number" wire:model="paymentAmount" placeholder="Enter amount" min="1" max="{{ $order->remaining_balance }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('paymentAmount') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                <p class="text-xs text-gray-600 mt-1">Max: ₱{{ number_format($order->remaining_balance) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="cash">Cash</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Record Payment
                                </button>
                                <button type="button" wire:click="togglePaymentForm" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @else
                        <button wire:click="togglePaymentForm" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition">
                            Record Payment
                        </button>
                    @endif
                </div>
            @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="font-semibold text-green-900">Fully Paid</p>
                            <p class="text-sm text-green-700">All payments completed</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
