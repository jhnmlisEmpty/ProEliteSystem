<div>
    {{-- Header --}}
    <x-page-header title="Create Order" subtitle="Create and manage customer orders">
        <x-slot name="actions">
            <div class="text-right">
                <p class="text-sm text-gray-600">Order Total:</p>
                <p class="text-2xl font-bold text-blue-600">₱{{ number_format($total_due, 2) }}</p>
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
            <button wire:click="$toggle('showCustomerForm')" class="text-sm px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-md font-medium transition">
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
                <button wire:click="createNewCustomer" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition">
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
                            @if($vehicle_type || $plate_number)
                                <p class="text-sm text-gray-600">📍 {{ $vehicle_type }} {{ $plate_number ? '• '.$plate_number : '' }}</p>
                            @endif
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
                        <input type="text" wire:model.live="customerSearch" placeholder="By name or phone..." class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @if($customers->count() > 0)
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

    {{-- Order Summary Section (Top) --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="bg-slate-900 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h2 class="text-lg font-bold">📋 Order Summary</h2>
            <span class="text-2xl font-bold text-blue-300">₱{{ number_format($total_due, 2) }}</span>
        </div>

        <div class="p-6">
            @if(empty($cartItems))
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">Cart is empty</p>
                    <p class="text-sm">Add products, services, or expenses to get started</p>
                </div>
            @else
                <div class="space-y-4">
                    {{-- PRODUCTS SECTION --}}
                    @php
                        $summaryProducts = array_filter($cartItems, fn($item) => $item['type'] === 'product');
                    @endphp
                    @if(!empty($summaryProducts))
                        <div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">PRODUCTS</h3>
                            <div class="space-y-2">
                                @foreach($summaryProducts as $key => $item)
                                    <div class="flex justify-between items-start pb-2 border-b border-gray-100">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <button wire:click="updateItemQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold">−</button>
                                                <input type="number" min="1" wire:change="updateItemQuantity('{{ $key }}', $event.target.value)" value="{{ $item['quantity'] }}" class="w-12 px-2 py-1 border border-gray-300 rounded text-center text-sm">
                                                <button wire:click="updateItemQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold">+</button>
                                                <span class="text-xs text-gray-600 ml-2">₱{{ number_format($item['unit_price'], 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right ml-2">
                                            <button wire:click="removeItemFromCart('{{ $key }}')" class="text-red-600 hover:text-red-800 mb-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <p class="font-bold text-gray-900">₱{{ number_format($item['total_price'], 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- SERVICES SECTION --}}
                    @php
                        $summaryServices = array_filter($cartItems, fn($item) => $item['type'] === 'service');
                    @endphp
                    @if(!empty($summaryServices))
                        <div>
                            <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-2">SERVICES</h3>
                            <div class="space-y-2">
                                @foreach($summaryServices as $key => $item)
                                    <div class="flex justify-between items-start pb-2 border-b border-gray-100">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                            @if(!empty($item['crew_members']))
                                                <p class="text-xs text-gray-600 mb-1">
                                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    @php
                                                        $crewNames = collect($item['crew_members'])
                                                            ->map(fn($member) => is_array($member) ? $member['name'] : $member->name)
                                                            ->implode(', ');
                                                    @endphp
                                                    {{ $crewNames }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-2">
                                            <button wire:click="removeItemFromCart('{{ $key }}')" class="text-red-600 hover:text-red-800 mb-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <p class="font-bold text-gray-900">₱{{ number_format($item['total_price'], 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- EXPENSES SECTION --}}
                    @php
                        $summaryExpenses = array_filter($cartItems, fn($item) => $item['type'] === 'expense');
                    @endphp
                    @if(!empty($summaryExpenses))
                        <div>
                            <h3 class="text-xs font-bold text-orange-600 uppercase tracking-wide mb-2">MISC. EXPENSES</h3>
                            <div class="space-y-2">
                                @foreach($summaryExpenses as $key => $item)
                                    <div class="flex justify-between items-start pb-2 border-b border-gray-100">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                            @if($item['is_billable'])
                                                <p class="text-xs text-gray-600">My Cost: <span class="text-red-600">₱{{ number_format($item['my_cost'], 2) }}</span> • Charge: <span class="text-blue-600">₱{{ number_format($item['charge_client'], 2) }}</span></p>
                                            @else
                                                <p class="text-xs text-gray-600">Cost: <span class="text-red-600">₱{{ number_format($item['my_cost'], 2) }}</span></p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-2">
                                            <button wire:click="removeItemFromCart('{{ $key }}')" class="text-red-600 hover:text-red-800 mb-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <p class="font-bold text-gray-900">₱{{ number_format($item['total_price'], 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- DISCOUNT SECTION --}}
                    @if($discount_value > 0)
                        <div class="flex justify-between items-center py-2 text-red-600 font-bold border-t-2 border-red-200">
                            <span>Discount ({{ $discount_type === 'percentage' ? $discount_value . '%' : 'Fixed' }}) <button wire:click="$set('discount_value', 0)" class="text-xs ml-1">×</button></span>
                            <span>−₱{{ number_format($discounted_amount, 2) }}</span>
                        </div>
                    @endif

                    {{-- TOTALS --}}
                    <div class="space-y-2 pt-2 border-t-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">₱{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-blue-600 py-1">
                            <span>Total Due</span>
                            <span>₱{{ number_format($total_due, 2) }}</span>
                        </div>
                    </div>

                    {{-- DISCOUNT INPUT --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mt-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Apply Discount</label>
                        <div class="flex gap-2">
                            <select wire:model="discount_type" class="px-3 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="percentage">% Off</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                            <input type="number" min="0" step="0.01" wire:model.live="discount_value" placeholder="Amount" class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button wire:click="recalculate" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded font-medium transition">
                                Apply
                            </button>
                        </div>
                    </div>

                    {{-- CHECKOUT BUTTON --}}
                    @if($customer_id && !empty($cartItems))
                        <button wire:click="saveOrder" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition text-lg mt-4">
                            💳 Pay Now - ₱{{ number_format($total_due, 2) }}
                        </button>
                    @else
                        <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed mt-4">
                            {{ !$customer_id ? 'Select Customer' : 'Add Items' }}
                        </button>
                    @endif

                    {{-- ACTION BUTTONS --}}
                    <div class="flex gap-2 mt-3">
                        @if(!empty($cartItems))
                            <button wire:click="clearCart" class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-3 rounded transition">
                                Clear Cart
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Content: Products/Services + Cart --}}
    <div class="space-y-4">
        {{-- Products/Services Section --}}
        <div class="space-y-4">
            {{-- Tab Selection --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2">
                    <button wire:click="setTab('products')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'products' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Products ({{ $products->count() }})
                    </button>
                    <button wire:click="setTab('services')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'services' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        Services ({{ $services->count() }})
                    </button>
                    <button wire:click="setTab('expenses')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'expenses' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Expenses
                    </button>
                </div>
            </div>

            {{-- Search & Create --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2 mb-4">
                    @if($activeTab === 'products')
                        <input type="text" wire:model.live="productSearch" placeholder="Search products by name or SKU..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @endif
                </div>

                {{-- PRODUCTS TAB --}}
                @if($activeTab === 'products')
                    @if($products->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[500px] overflow-y-auto">
                            @foreach($products as $product)
                                <button wire:click="addProduct({{ $product->id }})" class="text-left p-4 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-400 rounded-lg transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold text-gray-900 flex-1">{{ $product->name }}</h4>
                                        <span class="text-blue-600 font-bold text-lg">₱{{ number_format($product->sell_price, 0) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-2">SKU: {{ $product->sku ?? 'N/A' }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs {{ ($product->stock_qty ?? 0) <= 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} px-2 py-1 rounded-full font-medium">
                                            Stock: {{ $product->stock_qty ?? 0 }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $product->category ?? 'Material' }}
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

                {{-- SERVICES TAB --}}
                @elseif($activeTab === 'services')
                    <div class="mb-4">
                        <div class="flex gap-2 mb-4">
                           <input type="text" wire:model.live="serviceSearch" placeholder="Search by existing service..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                       
                        
                        @if($serviceSearch && $services->count() > 0)
                            <div class="mt-2 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-auto">
                                @foreach($services as $service)
                                    <button type="button" wire:click="selectService({{ $service->id }})" class="w-full text-left px-3 py-2 hover:bg-blue-50 border-b last:border-b-0 transition">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-900">{{ $service->name }}</span>
                                            <span class="text-blue-600 font-bold text-sm">₱{{ number_format($service->base_labor_cost ?? 0, 2) }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Create/Add Service Form --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-1">
                            {{ $serviceSelectedId ? 'Add Service to Order' : 'Create New Service' }}
                        </h3>
                        @if(!empty($serviceCrew))
                            <p class="text-xs text-gray-600 mb-3">
                                <span class="font-medium">Assigned:</span> 
                                @foreach($serviceCrew as $crewId)
                                    @php
                                        $employee = $employees->firstWhere('id', $crewId);
                                    @endphp
                                    @if($employee)
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs mr-1">{{ $employee->name }}</span>
                                    @endif
                                @endforeach
                            </p>
                        @endif

                        <div class="space-y-3">
                            {{-- Service Name --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Service Name *</label>
                                <input type="text" wire:model="serviceName" placeholder="e.g., Deep Interior Detailing" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('serviceName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Client Price --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Charge Client *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-400">₱</span>
                                    <input type="number" min="0" step="0.01" wire:model="serviceClientPrice" placeholder="0.00" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent font-bold text-blue-700">
                                </div>
                                @error('serviceClientPrice') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Assign Crew --}}
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Assign Crew (Optional)</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($employees as $employee)
                                        @php
                                            $isAssigned = in_array($employee->id, $serviceCrew);
                                        @endphp
                                        <button type="button"
                                            wire:click="toggleCrew({{ $employee->id }})" 
                                            class="px-2 py-1 rounded text-xs font-medium transition {{ $isAssigned ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                            {{ $employee->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Add Button --}}
                            <button type="button" wire:click="addService" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                                {{ $serviceSelectedId ? '+ Add Service to Order' : '+ Create & Add Service' }}
                            </button>

                            {{-- Clear/Reset Button --}}
                            @if($serviceSelectedId || $serviceName || $serviceClientPrice > 0 || !empty($serviceCrew))
                                <button type="button" wire:click="clearServiceForm" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-md transition text-sm">
                                    Clear Form
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Item Description</label>
                            <input type="text" wire:model="expenseDescription" placeholder="e.g., Rush Fee, Special Bulb" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-red-50 p-3 rounded border border-red-100">
                                <label class="block text-xs font-bold text-red-800 uppercase mb-1">My Cost</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-red-400 text-sm">₱</span>
                                    <input type="number" min="0" step="0.01" wire:model="expenseMyCost" placeholder="0.00" class="w-full pl-6 pr-3 py-2 text-sm border border-red-200 rounded focus:outline-none focus:border-red-500 bg-white text-red-600 font-medium">
                                </div>
                            </div>
                            <div class="bg-blue-50 p-3 rounded border border-blue-100">
                                <div class="flex justify-between mb-1">
                                    <label class="block text-xs font-bold text-blue-800 uppercase">Charge Client</label>
                                    <label class="flex items-center gap-1 cursor-pointer text-[10px] text-blue-600">
                                        <input type="checkbox" class="w-3 h-3 text-blue-600 rounded" wire:click="$toggle('expenseBillable')">
                                        <span>Billable</span>
                                    </label>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-blue-400 text-sm">₱</span>
                                    <input type="number" min="0" step="0.01" wire:model="expenseChargeClient" placeholder="0.00" class="w-full pl-6 pr-3 py-2 text-sm border border-blue-200 rounded focus:outline-none focus:border-blue-500 bg-white text-blue-600 font-bold" @disabled(!$expenseBillable)>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="addExpense" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-2 rounded text-sm transition">
                            Add Expense
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
