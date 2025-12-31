<div>
    {{-- Header --}}
    <x-page-header title="Point of Sale" subtitle="Create and manage customer orders">
        <x-slot name="actions">
            <div class="text-right">
                <p class="text-sm text-gray-600">Order Total:</p>
                <p class="text-2xl font-bold text-blue-600">₱{{ number_format($this->cartTotal) }}</p>
            </div>
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

    {{-- Customer Selection Section --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">Customer</h2>
            </div>
            <button wire:click="toggleCustomerForm" class="text-sm px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-md font-medium transition">
                {{ $showCustomerForm ? '✕ Cancel' : '+ New Customer' }}
            </button>
        </div>

        @if($showCustomerForm)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <h3 class="font-medium text-gray-900 mb-3">Create New Customer</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <input type="text" wire:model="newCustomerName" placeholder="Full Name *" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <input type="text" wire:model="newCustomerPhone" placeholder="Phone Number *" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <textarea wire:model="newCustomerAddress" placeholder="Address *" rows="1" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                </div>
                <div class="flex gap-2 text-red-600 text-xs mb-2">
                    @error('newCustomerName') <span>Name required</span> @enderror
                    @error('newCustomerPhone') <span>Phone required</span> @enderror
                    @error('newCustomerAddress') <span>Address required</span> @enderror
                </div>
                <button wire:click="createCustomer" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition">
                    Create & Select Customer
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($customer_id)
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div>
                            <p class="text-xs text-gray-600">Selected Customer</p>
                            <p class="font-semibold text-gray-900">{{ $customer_name }}</p>
                            <p class="text-sm text-gray-600">{{ $customers->firstWhere('id', $customer_id)?->phone ?? 'N/A' }}</p>
                        </div>
                        <button wire:click="$set('customer_id', '')" class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-md transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Customers</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="By name or phone..." class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @if($customerSearch && count($customers) > 0)
                            <div class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-auto">
                                @foreach($customers as $customer)
                                    <button wire:click="selectCustomer({{ $customer->id }})" class="w-full text-left px-4 py-2 hover:bg-blue-50 border-b last:border-b-0 transition">
                                        <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($customer_id)
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vehicle Type</label>
                        <input type="text" wire:model="vehicle_type" placeholder="e.g., Ford Transit" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Plate Number</label>
                        <input type="text" wire:model="plate_number" placeholder="e.g., ABC-1234" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Content: Products/Services + Cart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Products/Services Section --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Tab Selection --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2">
                    <button wire:click="$set('searchType', 'product')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $searchType === 'product' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Products ({{ count($products) }})
                    </button>
                    <button wire:click="$set('searchType', 'service')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $searchType === 'service' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        Services ({{ count($services) }})
                    </button>
                </div>
            </div>

            {{-- Search & Create --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2 mb-4">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Search {{ $searchType === 'product' ? 'products by name or SKU' : 'services by name' }}..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @if($searchType === 'service' && !$showServiceForm)
                        <button wire:click="toggleServiceForm" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md font-medium transition whitespace-nowrap">
                            + New Service
                        </button>
                    @endif
                </div>

                @if($searchType === 'service' && $showServiceForm)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Create Service</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                            <input type="text" wire:model="newServiceName" placeholder="Service Name *" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <input type="number" wire:model="newServiceCost" placeholder="Cost *" step="1" min="0" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="createService" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition text-sm">
                                Create
                            </button>
                            <button wire:click="toggleServiceForm" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Items Grid --}}
                @if($searchType === 'product')
                    @if(count($products) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[500px] overflow-y-auto">
                            @foreach($products as $product)
                                <button wire:click="addToCart({{ $product->id }})" class="text-left p-4 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-400 rounded-lg transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold text-gray-900 flex-1">{{ $product->name }}</h4>
                                        <span class="text-blue-600 font-bold text-lg">₱{{ number_format($product->sell_price) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-2">SKU: {{ $product->sku }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs {{ $product->stock_qty <= $product->alert_limit ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} px-2 py-1 rounded-full font-medium">
                                            Stock: {{ $product->stock_qty }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $product->type === 'retail' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($product->type) }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="font-medium">No products available</p>
                        </div>
                    @endif
                @else
                    @if(count($services) > 0)
                        <div class="space-y-2 max-h-[500px] overflow-y-auto">
                            @foreach($services as $service)
                                <button wire:click="addToCart({{ $service->id }})" class="w-full text-left p-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-400 rounded-lg transition">
                                    <div class="flex justify-between items-center">
                                        <h4 class="font-semibold text-gray-900">{{ $service->name }}</h4>
                                        <span class="text-blue-600 font-bold">₱{{ number_format($service->base_labor_cost) }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            <p class="font-medium">No services available</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Cart Section --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-24 max-h-[calc(100vh-150px)] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Shopping Cart</h3>
                    @if(count($cart) > 0)
                        <button wire:click="clearCart" class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded transition">
                            Clear
                        </button>
                    @endif
                </div>

                @if(count($cart) === 0)
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">Cart is empty</p>
                    </div>
                @else
                    <div class="space-y-2 mb-4">
                        @foreach($cart as $key => $item)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 text-sm">{{ $item['name'] }}</p>
                                        <p class="text-xs text-gray-600">
                                            @if($item['type'] === 'product')
                                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs mr-1">Product</span>
                                            @else
                                                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-xs mr-1">Service</span>
                                            @endif
                                        </p>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')" class="text-red-600 hover:text-red-800 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <input type="number" wire:change="updateQuantity('{{ $key }}', $event.target.value)" value="{{ $item['quantity'] }}" min="1" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600">₱{{ number_format($item['unit_price']) }}</p>
                                        <p class="font-bold text-gray-900">₱{{ number_format($item['total_price']) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Summary --}}
                <div class="border-t pt-4 space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Items</span>
                        <span class="font-medium">{{ count($cart) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Qty</span>
                        <span class="font-medium">{{ collect($cart)->sum('quantity') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-blue-600">₱{{ number_format($this->cartTotal) }}</span>
                    </div>
                </div>

                {{-- Checkout Button --}}
                @if($customer_id && count($cart) > 0)
                    <button wire:click="checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Create Order
                    </button>
                @else
                    <button disabled class="w-full bg-gray-300 text-gray-500 font-semibold py-3 px-4 rounded-lg cursor-not-allowed">
                        {{ !$customer_id ? 'Select Customer' : 'Add Items' }}
                    </button>
                @endif

                <p class="text-xs text-gray-600 mt-3 text-center">
                    Stock auto-deducted • Job order created
                </p>
            </div>
        </div>
    </div>
</div>

