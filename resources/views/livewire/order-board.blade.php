<div>
    {{-- Header --}}
    <x-page-header title="Job Order Board" subtitle="Manage service orders and track progress">
        <x-slot name="actions">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                View All Orders
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

    {{-- Kanban Board --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Pending Column --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pending</h2>
                <span class="bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full">{{ count($pendingOrders) }}</span>
            </div>

            <div class="space-y-3">
                @forelse($pendingOrders as $order)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition cursor-pointer border-l-4 border-yellow-400" wire:click="$navigate('{{ route('orders.view', $order->order_id) }}')">
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Order #{{ $order->order_id }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $order->order->customer_name ?? 'N/A' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </div>

                            @if($order->order->orderItems->count() > 0)
                                <div class="text-xs text-gray-600 mb-3">
                                    <p class="font-medium text-gray-700">Services:</p>
                                    @forelse($order->order->orderItems as $item)
                                        @if($item->service_id)
                                            <p class="ml-2">• {{ $item->item_name ?? $item->service->name ?? 'N/A' }}</p>
                                        @endif
                                    @empty
                                        <p class="ml-2 text-gray-500">No services</p>
                                    @endforelse
                                </div>
                            @endif

                            <div class="flex gap-2 mt-3">
                                <button wire:click.stop="updateStatus({{ $order->id }}, 'in_progress')" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs font-medium transition">
                                    Start
                                </button>
                                <a href="{{ route('orders.view', $order->order_id) }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-medium transition text-center">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg p-8 text-center">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">No pending orders</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- In Progress Column --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">In Progress</h2>
                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">{{ count($inProgressOrders) }}</span>
            </div>

            <div class="space-y-3">
                @forelse($inProgressOrders as $order)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition cursor-pointer border-l-4 border-blue-400" wire:click="$navigate('{{ route('orders.view', $order->order_id) }}')">
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Order #{{ $order->order_id }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $order->order->customer_name ?? 'N/A' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    In Progress
                                </span>
                            </div>

                            @if($order->order->orderItems->count() > 0)
                                <div class="text-xs text-gray-600 mb-3">
                                    <p class="font-medium text-gray-700">Services:</p>
                                    @foreach($order->order->orderItems->where('service_id', '!=', null) as $item)
                                        <p class="ml-2">• {{ $item->item_name }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex gap-2 mt-3">
                                <button wire:click.stop="updateStatus({{ $order->id }}, 'completed')" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded text-xs font-medium transition">
                                    Complete
                                </button>
                                <a href="{{ route('orders.view', $order->order_id) }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-medium transition text-center">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg p-8 text-center">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">No orders in progress</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Completed Column --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Completed</h2>
                <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">{{ count($completedOrders) }}</span>
            </div>

            <div class="space-y-3">
                @forelse($completedOrders as $order)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition cursor-pointer border-l-4 border-green-400" wire:click="$navigate('{{ route('orders.view', $order->order_id) }}')">
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Order #{{ $order->order_id }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $order->order->customer_name ?? 'N/A' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Completed
                                </span>
                            </div>

                            @if($order->order->orderItems->count() > 0)
                                <div class="text-xs text-gray-600 mb-3">
                                    <p class="font-medium text-gray-700">Services:</p>
                                    @foreach($order->order->orderItems->where('service_id', '!=', null) as $item)
                                        <p class="ml-2">• {{ $item->item_name }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ route('orders.view', $order->order_id) }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-medium transition text-center">
                                View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg p-8 text-center">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">No completed orders</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ count($pendingOrders) }}</p>
                </div>
                <svg class="w-12 h-12 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900">{{ count($inProgressOrders) }}</p>
                </div>
                <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-3xl font-bold text-gray-900">{{ count($completedOrders) }}</p>
                </div>
                <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>
