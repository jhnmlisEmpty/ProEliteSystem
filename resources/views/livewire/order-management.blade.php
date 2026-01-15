<div>
    {{-- Header --}}
    <x-page-header title="Order Management" subtitle="Manage all customer orders">
        <x-slot name="actions">
            <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Order
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

    {{-- View Toggle --}}
    <div class="mb-6 flex gap-4 items-center">
        <div class="flex gap-2 bg-white rounded-lg shadow p-1">
            <button 
                wire:click="setView('table')" 
                class="px-4 py-2 rounded-md font-medium transition {{ $view === 'table' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Table
            </button>
            <button 
                wire:click="setView('kanban')" 
                class="px-4 py-2 rounded-md font-medium transition {{ $view === 'kanban' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4z"></path>
                </svg>
                Kanban
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-{{ $canFilterBranch ? '5' : '4' }} gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Order ID or customer..." class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                <select wire:model.live="paymentFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Payments</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order Items</label>
                <select wire:model.live="itemTypeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Items</option>
                    <option value="product">Products</option>
                    <option value="service">Services</option>
                    <option value="upholstery">Upholstery</option>
                    <option value="vip">VIP Packages</option>
                </select>
            </div>
            @if($canFilterBranch)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select wire:model.live="branchFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    {{-- TABLE VIEW --}}
    @if($view === 'table')
        {{-- Tabs --}}
        <div class="mb-6 border-b border-gray-200 bg-white rounded-t-lg">
            <div class="flex overflow-x-auto">
                <button 
                    wire:click="setTableTab('all')" 
                    class="px-6 py-4 font-medium text-sm whitespace-nowrap transition border-b-2 {{ $tableTab === 'all' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}"
                >
                    All Orders
                </button>
                <button 
                    wire:click="setTableTab('pending')" 
                    class="px-6 py-4 font-medium text-sm whitespace-nowrap transition border-b-2 {{ $tableTab === 'pending' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}"
                >
                    Pending
                </button>
                <button 
                    wire:click="setTableTab('in_progress')" 
                    class="px-6 py-4 font-medium text-sm whitespace-nowrap transition border-b-2 {{ $tableTab === 'in_progress' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}"
                >
                    In Progress
                </button>
                <button 
                    wire:click="setTableTab('completed')" 
                    class="px-6 py-4 font-medium text-sm whitespace-nowrap transition border-b-2 {{ $tableTab === 'completed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}"
                >
                    Completed
                </button>
                <button 
                    wire:click="setTableTab('cancelled')" 
                    class="px-6 py-4 font-medium text-sm whitespace-nowrap transition border-b-2 {{ $tableTab === 'cancelled' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}"
                >
                    Cancelled
                </button>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Order Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900">#{{ $order->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $order->customer_name }}</div>
                                <div class="text-sm text-gray-600">{{ $order->customer?->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">
                                    {{ $order->branch?->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->customer && ($order->customer->vehicle_type || $order->customer->plate_number))
                                    <div class="text-sm text-gray-900">{{ $order->customer->vehicle_type }}</div>
                                    <div class="text-sm text-gray-600">{{ $order->customer->plate_number }}</div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $hasProducts = $order->orderItems->where('product_id', '!=', null)->count() > 0;
                                        $hasServices = $order->orderItems->where('service_id', '!=', null)->count() > 0;
                                        $hasUpholstery = $order->orderItems->where('upholstery_id', '!=', null)->count() > 0;
                                        $hasVip = $order->orderItems->where('vip_id', '!=', null)->count() > 0;
                                        $hasExpenses = $order->expenses->count() > 0;
                                    @endphp
                                    @if($hasProducts)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Products</span>
                                    @endif
                                    @if($hasServices)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Services</span>
                                    @endif
                                    @if($hasUpholstery)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Upholstery</span>
                                    @endif
                                    @if($hasVip)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">VIP</span>
                                    @endif
                                    @if($hasExpenses)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Expenses</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900">₱{{ number_format($order->total_amount) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $totalPaid = $order->payments()->sum('amount') ?? 0;
                                    $balance = $order->total_amount - $totalPaid;
                                @endphp
                                <span class="font-semibold {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }}">₱{{ number_format($balance) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($order->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($order->payment_status === 'unpaid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unpaid</span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Partial</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Paid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('orders.view', $order->id) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">View</a>
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('orders.edit', $order->id) }}" class="text-gray-700 hover:text-gray-900 font-medium text-sm">Edit</a>
                                        @if($order->status === 'pending')
                                            <button wire:click="delete({{ $order->id }})" wire:confirm="Are you sure you want to delete this order?" class="text-red-600 hover:text-red-700 font-medium text-sm">Delete</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No orders found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden space-y-4">
            @forelse($orders as $order)
                <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="font-semibold text-gray-900">Order #{{ $order->id }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $order->customer_name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">₱{{ number_format($order->total_amount) }}</div>
                            @php
                                $totalPaid = $order->payments()->sum('amount') ?? 0;
                                $balance = $order->total_amount - $totalPaid;
                            @endphp
                            <div class="text-sm {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }} font-medium">Balance: ₱{{ number_format($balance) }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">
                            {{ $order->branch?->name ?? 'N/A' }}
                        </span>

                        @if($order->status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($order->status === 'in_progress')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                        @elseif($order->status === 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                        @endif

                        @if($order->payment_status === 'unpaid')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unpaid</span>
                        @elseif($order->payment_status === 'partial')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Partial</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Paid</span>
                        @endif
                    </div>

                    @if($order->customer && ($order->customer->vehicle_type || $order->customer->plate_number))
                        <div class="text-sm text-gray-600 mb-4 pb-4 border-b border-gray-200">
                            {{ $order->customer->vehicle_type }} - {{ $order->customer->plate_number }}
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('orders.view', $order->id) }}" class="flex-1 text-center bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition">View</a>
                        @if(auth()->user()->role === 'admin')
                            @if(in_array($order->status, ['pending','in_progress']))
                                <a href="{{ route('orders.edit', $order->id) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition">Edit</a>
                            @endif
                            @if($order->status === 'pending')
                                <button wire:click="delete({{ $order->id }})" wire:confirm="Are you sure?" class="flex-1 text-center bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition">Delete</button>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">No orders found</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($orders->count() > 0)
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    @endif

    {{-- KANBAN VIEW --}}
    @if($view === 'kanban')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Pending Column --}}
            <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="bg-yellow-100 border-b-4 border-yellow-400 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-yellow-900 text-lg">Pending</h3>
                            <p class="text-sm text-yellow-700 mt-1">{{ $showAllPending ? $pendingTotal : ($pendingTotal > 10 ? '10 out of ' . $pendingTotal : $pendingTotal) }} orders</p>
                        </div>
                        @if(!$showAllPending && $pendingOrders->count() >= 10)
                            <button wire:click="toggleShowAllPending" class="text-xs bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show All
                            </button>
                        @elseif($showAllPending)
                            <button wire:click="toggleShowAllPending" class="text-xs bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show Less
                            </button>
                        @endif
                    </div>
                </div>
                <div class="p-4 space-y-3 flex-1">
                    @forelse($pendingOrders as $order)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="font-semibold text-gray-900 text-lg">Order #{{ $order->id }}</div>
                                <div class="flex flex-wrap gap-1 ml-2">
                                    @php
                                        $hasProducts = $order->orderItems->where('product_id', '!=', null)->count() > 0;
                                        $hasServices = $order->orderItems->where('service_id', '!=', null)->count() > 0;
                                        $hasUpholstery = $order->orderItems->where('upholstery_id', '!=', null)->count() > 0;
                                        $hasVip = $order->orderItems->where('vip_id', '!=', null)->count() > 0;
                                        $hasExpenses = $order->expenses->count() > 0;
                                    @endphp
                                    @if($hasProducts)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">PRODUCTS</span>
                                    @endif
                                    @if($hasServices)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">SERVICES</span>
                                    @endif
                                    @if($hasUpholstery)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">UPHOLSTERY</span>
                                    @endif
                                    @if($hasVip)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">VIP PACKAGE</span>
                                    @endif
                                    @if($hasExpenses)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">EXPENSES</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="font-semibold text-gray-900">₱{{ number_format($order->total_amount) }}</div>
                                </div>
                                <div class="text-right">
                                    @php
                                        $totalPaid = $order->payments()->sum('amount') ?? 0;
                                        $balance = $order->total_amount - $totalPaid;
                                    @endphp
                                    <div class="text-sm {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }} font-medium">Balance: ₱{{ number_format($balance) }}</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-600 mb-2">{{ $order->customer?->phone ?? 'N/A' }}</div>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">{{ $order->branch?->name ?? 'N/A' }}</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">Pending</span>
                                @if($order->payment_status === 'unpaid')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">Unpaid</span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded">Partial</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Paid</span>
                                @endif
                            </div>
                            @if($order->customer && ($order->customer->vehicle_type || $order->customer->plate_number))
                                <div class="text-sm text-gray-600 mb-3">{{ $order->customer->vehicle_type }} - {{ $order->customer->plate_number }}</div>
                            @endif
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'in_progress')" class="px-2 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition">In Progress</button>
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'completed')" class="px-2 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded font-medium transition">Complete</button>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('orders.view', $order->id) }}" class="flex-1 text-center text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg font-medium transition">View</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('orders.edit', $order->id) }}" class="flex-1 text-center text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition">Edit</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No pending orders</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- In Progress Column --}}
            <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="bg-blue-100 border-b-4 border-blue-400 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-blue-900 text-lg">In Progress</h3>
                            <p class="text-sm text-blue-700 mt-1">{{ $showAllInProgress ? $inProgressTotal : ($inProgressTotal > 10 ? '10 out of ' . $inProgressTotal : $inProgressTotal) }} orders</p>
                        </div>
                        @if(!$showAllInProgress && $inProgressOrders->count() >= 10)
                            <button wire:click="toggleShowAllInProgress" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show All
                            </button>
                        @elseif($showAllInProgress)
                            <button wire:click="toggleShowAllInProgress" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show Less
                            </button>
                        @endif
                    </div>
                </div>
                <div class="p-4 space-y-3 flex-1">
                    @forelse($inProgressOrders as $order)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="font-semibold text-gray-900 text-lg">Order #{{ $order->id }}</div>
                                <div class="flex flex-wrap gap-1 ml-2">
                                    @php
                                        $hasProducts = $order->orderItems->where('product_id', '!=', null)->count() > 0;
                                        $hasServices = $order->orderItems->where('service_id', '!=', null)->count() > 0;
                                        $hasUpholstery = $order->orderItems->where('upholstery_id', '!=', null)->count() > 0;
                                        $hasVip = $order->orderItems->where('vip_id', '!=', null)->count() > 0;
                                        $hasExpenses = $order->expenses->count() > 0;
                                    @endphp
                                    @if($hasProducts)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">PRODUCTS</span>
                                    @endif
                                    @if($hasServices)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">SERVICES</span>
                                    @endif
                                    @if($hasUpholstery)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">UPHOLSTERY</span>
                                    @endif
                                    @if($hasVip)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">VIP PACKAGE</span>
                                    @endif
                                    @if($hasExpenses)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">EXPENSES</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="font-semibold text-gray-900">₱{{ number_format($order->total_amount) }}</div>
                                </div>
                                <div class="text-right">
                                    @php
                                        $totalPaid = $order->payments()->sum('amount') ?? 0;
                                        $balance = $order->total_amount - $totalPaid;
                                    @endphp
                                    <div class="text-sm {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }} font-medium">Balance: ₱{{ number_format($balance) }}</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-600 mb-2">{{ $order->customer?->phone ?? 'N/A' }}</div>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">{{ $order->branch?->name ?? 'N/A' }}</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">In Progress</span>
                                @if($order->payment_status === 'unpaid')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">Unpaid</span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded">Partial</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Paid</span>
                                @endif
                            </div>
                            @if($order->customer && ($order->customer->vehicle_type || $order->customer->plate_number))
                                <div class="text-sm text-gray-600 mb-3">{{ $order->customer->vehicle_type }} - {{ $order->customer->plate_number }}</div>
                            @endif
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'completed')" class="px-2 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded font-medium transition">Complete</button>
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'pending')" class="px-2 py-1.5 text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded font-medium transition">Pending</button>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('orders.view', $order->id) }}" class="flex-1 text-center text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg font-medium transition">View</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('orders.edit', $order->id) }}" class="flex-1 text-center text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition">Edit</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No in-progress orders</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Completed Column --}}
            <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="bg-green-100 border-b-4 border-green-400 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-green-900 text-lg">Completed</h3>
                            <p class="text-sm text-green-700 mt-1">{{ $showAllCompleted ? $completedTotal : ($completedTotal > 10 ? '10 out of ' . $completedTotal : $completedTotal) }} orders</p>
                        </div>
                        @if(!$showAllCompleted && $completedOrders->count() >= 10)
                            <button wire:click="toggleShowAllCompleted" class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show All
                            </button>
                        @elseif($showAllCompleted)
                            <button wire:click="toggleShowAllCompleted" class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded font-medium transition">
                                Show Less
                            </button>
                        @endif
                    </div>
                </div>
                <div class="p-4 space-y-3 flex-1">
                    @forelse($completedOrders as $order)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="font-semibold text-gray-900 text-lg">Order #{{ $order->id }}</div>
                                <div class="flex flex-wrap gap-1 ml-2">
                                    @php
                                        $hasProducts = $order->orderItems->where('product_id', '!=', null)->count() > 0;
                                        $hasServices = $order->orderItems->where('service_id', '!=', null)->count() > 0;
                                        $hasUpholstery = $order->orderItems->where('upholstery_id', '!=', null)->count() > 0;
                                        $hasVip = $order->orderItems->where('vip_id', '!=', null)->count() > 0;
                                        $hasExpenses = $order->expenses->count() > 0;
                                    @endphp
                                    @if($hasProducts)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">PRODUCTS</span>
                                    @endif
                                    @if($hasServices)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">SERVICES</span>
                                    @endif
                                    @if($hasUpholstery)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">UPHOLSTERY</span>
                                    @endif
                                    @if($hasVip)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">VIP PACKAGE</span>
                                    @endif
                                    @if($hasExpenses)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">EXPENSES</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="font-semibold text-gray-900">₱{{ number_format($order->total_amount) }}</div>
                                </div>
                                <div class="text-right">
                                    @php
                                        $totalPaid = $order->payments()->sum('amount') ?? 0;
                                        $balance = $order->total_amount - $totalPaid;
                                    @endphp
                                    <div class="text-sm {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }} font-medium">Balance: ₱{{ number_format($balance) }}</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-600 mb-2">{{ $order->customer?->phone ?? 'N/A' }}</div>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">{{ $order->branch?->name ?? 'N/A' }}</span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Completed</span>
                                @if($order->payment_status === 'unpaid')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">Unpaid</span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded">Partial</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Paid</span>
                                @endif
                            </div>
                            @if($order->customer && ($order->customer->vehicle_type || $order->customer->plate_number))
                                <div class="text-sm text-gray-600 mb-3">{{ $order->customer->vehicle_type }} - {{ $order->customer->plate_number }}</div>
                            @endif
                            <div class="flex gap-2">
                                <a href="{{ route('orders.view', $order->id) }}" class="flex-1 text-center text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg font-medium transition">View</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('orders.edit', $order->id) }}" class="flex-1 text-center text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition">Edit</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No completed orders</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
