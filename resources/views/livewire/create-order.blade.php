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
        </div>

        {{-- Search existing customers to prefill form --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                {{-- Search Input --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" wire:model.live="customerSearch" placeholder="By name/phone..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        @if($customers->count() > 0)
                            <div class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-sm max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach($customers as $customer)
                                    <button wire:click="selectCustomer({{ $customer->id }})" type="button" class="w-full px-3 py-2 text-sm hover:bg-gray-50 transition text-left">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $customer->phone }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Full Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" wire:model="newCustomerName" placeholder="Name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    @error('newCustomerName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                    <input type="text" wire:model="newCustomerPhone" placeholder="Phone" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    @error('newCustomerPhone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea wire:model="newCustomerAddress" placeholder="Address" rows="1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                </div>
            </div>

            <div class="mt-3">
                <button wire:click="createNewCustomer" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition text-sm">
                    {{ $customer_id ? 'Update Customer' : 'Create & Select Customer' }}
                </button>
            </div>
        </div>

        @if($customer_id)
            <div class="flex gap-3 mb-4">
                <div class="flex-1 bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="font-semibold text-gray-900 text-sm">{{ $customer_name }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $newCustomerPhone }} | {{ $newCustomerAddress }}</p>
                </div>
                <button wire:click="$set('customer_id', '')" class="text-red-600 hover:text-red-800 px-3 py-2 rounded-lg hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Branch Selection --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <label class="block text-xs font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                <div class="flex gap-2 flex-wrap">
                    @foreach($branches as $branch)
                        <button 
                            wire:click="$set('branch_id', {{ $branch->id }})"
                            {{ !$canSelectBranch ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-md font-medium transition text-sm {{ $branch_id == $branch->id ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 border border-gray-300 hover:border-blue-400 hover:bg-blue-50' }} {{ !$canSelectBranch ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            {{ $branch->name }}
                        </button>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ $canSelectBranch ? 'Select the branch for this order' : 'Your branch is automatically assigned' }}</p>
                @error('branch_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    {{-- Main Content: Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Products/Services/Expenses --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Tab Selection --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2">
                    <button wire:click="setTab('products')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'products' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Products
                    </button>
                    <button wire:click="setTab('services')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'services' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        Services
                    </button>
                    <button wire:click="setTab('upholstery')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'upholstery' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                       <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V7a2 2 0 012-2h6a2 2 0 012 2v2M7 11a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Upholstery
                    </button>
                    <button wire:click="setTab('vip')" class="flex-1 px-4 py-3 rounded-md font-medium transition {{ $activeTab === 'vip' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        VIP
                    </button>
                </div>
            </div>

            {{-- Search & Products/Services/Expenses Content --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex gap-2 mb-4">
                    @if($activeTab === 'products')
                        <input type="text" wire:model.live="productSearch" placeholder="Search products by name or SKU..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @endif
                </div>

                {{-- PRODUCTS TAB --}}
                @if($activeTab === 'products')
                    @if(!$branch_id)
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="font-medium">Please select a branch first</p>
                            <p class="text-sm">Scroll up and choose a branch to view available products</p>
                        </div>
                    @elseif($products->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[500px] overflow-y-auto">
                            @foreach($products as $product)
                                @php
                                    $isDifferentBranch = $branch_id && $product->branch_id != $branch_id;
                                    $isDisabled = ($product->stock_qty ?? 0) <= 0 || $isDifferentBranch;
                                @endphp
                                <button 
                                    wire:click="addProduct({{ $product->id }})" 
                                    {{ $isDisabled ? 'disabled' : '' }}
                                    class="text-left p-4 rounded-lg transition {{ $isDisabled ? 'bg-red-50 border border-red-300 cursor-not-allowed opacity-60' : 'bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-400 cursor-pointer' }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold {{ $isDisabled ? 'text-red-900' : 'text-gray-900' }} flex-1">{{ $product->name }}</h4>
                                        <span class="{{ $isDisabled ? 'text-red-600' : 'text-blue-600' }} font-bold text-lg">₱{{ number_format($product->sell_price, 0) }}</span>
                                    </div>
                                    <p class="text-xs {{ $isDisabled ? 'text-red-600' : 'text-gray-600' }} mb-2">SKU: {{ $product->sku ?? 'N/A' }}</p>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs {{ $isDisabled ? 'bg-red-200 text-red-800' : (($product->stock_qty ?? 0) <= 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }} px-2 py-1 rounded-full font-medium">
                                            Stock: {{ $product->stock_qty ?? 0 }} {{ ($product->stock_qty ?? 0) <= 0 ? '(Out of Stock)' : '' }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $product->category ?? 'Material' }}
                                        </span>
                                    </div>
                                    <p class="text-xs {{ $isDifferentBranch ? 'text-red-600 font-semibold' : 'text-gray-500' }} pt-2 border-t border-gray-200">
                                        <span class="font-semibold">Branch:</span> {{ $product->branch?->name ?? 'N/A' }}
                                        @if($isDifferentBranch)
                                            <span class="block text-red-600 text-xs mt-1">⚠ Cannot add - different branch</span>
                                        @endif
                                    </p>
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
                            <div class="mt-2 bg-white border border-gray-200 rounded-md shadow-sm max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach($services as $service)
                                    <button type="button" wire:click="selectService({{ $service->id }})" class="w-full px-3 py-2 text-sm hover:bg-gray-50 transition text-left">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-900">{{ $service->name }}</span>
                                            <span class="text-gray-600 font-medium">₱{{ number_format($service->base_labor_cost ?? 0, 2) }}</span>
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
                @elseif($activeTab === 'upholstery')
                    {{-- UPHOLSTERY TAB --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V7a2 2 0 012-2h6a2 2 0 012 2v2M7 11a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Upholstery Service
                        </h3>

                        <div class="space-y-4">
                            {{-- Year Model --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle/Unit Type/Year Model <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="upholsteryYearModel" placeholder="e.g., 2020 Toyota Vios" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('upholsteryYearModel') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Installation Date --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Date of Installation <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="upholsteryInstallationDate" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('upholsteryInstallationDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Services Checkboxes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Services <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.seat_cover" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Seat Cover</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.ceiling" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Ceiling</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.sidings" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Sidings</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.rubber_mattings" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Rubber Mattings</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.front_mattings" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Front Mattings</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="upholsteryServices.headrest" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Headrest</span>
                                    </label>
                                </div>
                                @error('upholsteryServices') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea wire:model="upholsteryDescription" rows="3" placeholder="Additional details or special requests..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>

                            {{-- Photo Upload --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Photo (Optional)</label>
                                <input type="file" wire:model="upholsteryPhoto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('upholsteryPhoto') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="upholsteryPhoto" class="mt-2 text-sm text-gray-600">
                                    Uploading photo...
                                </div>

                            </div>

                            {{-- Total Amount --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-400">₱</span>
                                    <input type="number" min="1" wire:model="upholsteryTotalAmount" placeholder="0" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent font-bold text-blue-700">
                                </div>
                                @error('upholsteryTotalAmount') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Add Button --}}
                            <button type="button" wire:click="addUpholstery" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                                + Add Upholstery to Order
                            </button>

                            {{-- Clear Button --}}
                            @if($upholsteryYearModel || $upholsteryInstallationDate || $upholsteryTotalAmount > 0)
                                <button type="button" wire:click="clearUpholsteryForm" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-md transition text-sm">
                                    Clear Form
                                </button>
                            @endif
                        </div>
                    </div>
                @elseif($activeTab === 'vip')
                    {{-- VIP TAB --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            VIP Package
                        </h3>

                        <div class="space-y-4">
                            {{-- Stepboard Section --}}
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-2">Stepboard</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pcs</label>
                                        <input type="number" min="0" wire:model.live="vipStepboardPcs" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (₱)</label>
                                        <input type="number" min="0" wire:model.live="vipStepboardUnitPrice" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sub Amount (₱)</label>
                                        <input type="number" min="0" wire:model="vipStepboardAmount" readonly placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50 font-semibold text-blue-700">
                                    </div>
                                </div>
                            </div>

                            {{-- Engine Bay Section --}}
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-2">Engine Bay</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pcs</label>
                                        <input type="number" min="0" wire:model.live="vipEngineBayPcs" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (₱)</label>
                                        <input type="number" min="0" wire:model.live="vipEngineBayUnitPrice" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sub Amount (₱)</label>
                                        <input type="number" min="0" wire:model="vipEngineBayAmount" readonly placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50 font-semibold text-blue-700">
                                    </div>
                                </div>
                            </div>

                            {{-- Console Box Section --}}
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-2">Console Box</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pcs</label>
                                        <input type="number" min="0" wire:model.live="vipConsoleBoxPcs" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (₱)</label>
                                        <input type="number" min="0" wire:model.live="vipConsoleBoxUnitPrice" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sub Amount (₱)</label>
                                        <input type="number" min="0" wire:model="vipConsoleBoxAmount" readonly placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50 font-semibold text-blue-700">
                                    </div>
                                </div>
                            </div>

                            {{-- Thai Ceiling Section --}}
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-2">Thai Ceiling</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pcs</label>
                                        <input type="number" min="0" wire:model.live="vipThaiCeilingPcs" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (₱)</label>
                                        <input type="number" min="0" wire:model.live="vipThaiCeilingUnitPrice" wire:change="calculateVipComponentTotal" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sub Amount (₱)</label>
                                        <input type="number" min="0" wire:model="vipThaiCeilingAmount" readonly placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50 font-semibold text-blue-700">
                                    </div>
                                </div>
                            </div>

                            {{-- Component Total Display --}}
                            @if($vipComponentTotal > 0)
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-sm font-semibold text-gray-700">Components Total: <span class="text-blue-600">₱{{ number_format($vipComponentTotal, 2) }}</span></p>
                                </div>
                            @endif

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea wire:model="vipDescription" rows="2" placeholder="Additional details or special notes..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>

                            {{-- Photo Upload --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Photo (Optional)</label>
                                <input type="file" wire:model="vipPhoto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('vipPhoto') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="vipPhoto" class="mt-2 text-sm text-gray-600">
                                    Uploading photo...
                                </div>
                            </div>

                            {{-- Total Amount --}}
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border-2 border-blue-300">
                                <label class="block text-sm font-bold text-blue-900 mb-2 uppercase">Total Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-2xl text-blue-600">₱</span>
                                    <input type="number" wire:model="vipTotalAmount" readonly placeholder="0" class="w-full pl-10 pr-3 py-3 border-2 border-blue-300 rounded-md text-2xl bg-white font-bold text-blue-700 cursor-not-allowed">
                                </div>
                                <p class="text-xs text-blue-700 mt-2 font-medium">Auto-calculated from components above</p>
                                @error('vipTotalAmount') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Add Button --}}
                            <button type="button" wire:click="addVip" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                                + Add VIP Package to Order
                            </button>

                            {{-- Clear Button --}}
                            @if($vipComponentTotal > 0 || $vipDescription || $vipTotalAmount > 0)
                                <button type="button" wire:click="clearVipForm" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-md transition text-sm">
                                    Clear Form
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <p class="text-gray-500">Select a tab to add items to your order.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: Order Summary Sidebar --}}
        <div class="lg:col-span-1">
           <div class="bg-white rounded-lg shadow mb-6">
                <div class="bg-slate-900 text-white p-4 rounded-t-lg flex justify-between items-center">
                    <h2 class="text-lg font-bold">Order Summary</h2>
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

                            {{-- UPHOLSTERY SECTION --}}
                            @php
                                $summaryUpholstery = array_filter($cartItems, fn($item) => $item['type'] === 'upholstery');
                            @endphp
                            @if(!empty($summaryUpholstery))
                                <div>
                                    <h3 class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-2">UPHOLSTERY</h3>
                                    <div class="space-y-2">
                                        @foreach($summaryUpholstery as $key => $item)
                                            <div class="flex justify-between items-start pb-2 border-b border-gray-100">
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                                    <p class="text-xs text-gray-600">Installation: {{ date('M d, Y', strtotime($item['installation_date'])) }}</p>
                                                    @if($item['description'])
                                                        <p class="text-xs text-gray-500 italic mt-1">{{ Str::limit($item['description'], 50) }}</p>
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

                            {{-- VIP SECTION --}}
                            @php
                                $summaryVip = array_filter($cartItems, fn($item) => $item['type'] === 'vip');
                            @endphp
                            @if(!empty($summaryVip))
                                <div>
                                    <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wide mb-2">VIP PACKAGE</h3>
                                    <div class="space-y-2">
                                        @foreach($summaryVip as $key => $item)
                                            <div class="flex justify-between items-start pb-2 border-b border-gray-100">
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                                    <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                                                        @if($item['stepboard_pcs'] > 0)
                                                            <p>Stepboard: {{ $item['stepboard_pcs'] }} pcs → ₱{{ number_format($item['stepboard_amount'], 0) }}</p>
                                                        @endif
                                                        @if($item['engine_bay_pcs'] > 0)
                                                            <p>Engine Bay: {{ $item['engine_bay_pcs'] }} pcs → ₱{{ number_format($item['engine_bay_amount'], 0) }}</p>
                                                        @endif
                                                        @if($item['console_box_pcs'] > 0)
                                                            <p>Console Box: {{ $item['console_box_pcs'] }} pcs → ₱{{ number_format($item['console_box_amount'], 0) }}</p>
                                                        @endif
                                                        @if(isset($item['thai_ceiling_pcs']) && $item['thai_ceiling_pcs'] > 0)
                                                            <p>Thai Ceiling: {{ $item['thai_ceiling_pcs'] }} pcs → ₱{{ number_format($item['thai_ceiling_amount'], 0) }}</p>
                                                        @endif
                                                    </div>
                                                    @if($item['description'])
                                                        <p class="text-xs text-gray-500 italic mt-1">{{ Str::limit($item['description'], 50) }}</p>
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
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase">Apply Discount</label>
                                    <button 
                                        wire:click="toggleDiscountForm" 
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $showDiscountForm ? 'bg-blue-600' : 'bg-gray-300' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $showDiscountForm ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>

                                @if($showDiscountForm)
                                    {{-- Password Protection --}}
                                    @if(!$discountPasswordVerified)
                                        <div class="space-y-2 mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                            <p class="text-xs font-medium text-yellow-800">Admin password required</p>
                                            <input 
                                                type="password" 
                                                wire:model="discountPassword" 
                                                placeholder="Enter admin password" 
                                                class="w-full px-3 py-2 border border-yellow-300 rounded text-sm focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            <div class="flex gap-2">
                                                <button 
                                                    wire:click="verifyDiscountPassword" 
                                                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded text-sm font-medium transition">
                                                    Verify
                                                </button>
                                                <button 
                                                    wire:click="toggleDiscountForm" 
                                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 rounded text-sm font-medium transition">
                                                    Cancel
                                                </button>
                                            </div>
                                            @error('discountPassword') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    @else
                                        {{-- Discount Form --}}
                                        <div class="space-y-2">
                                            <select wire:model="discount_type" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="percentage">% Off</option>
                                                <option value="fixed">Fixed Amount</option>
                                            </select>
                                            <div class="flex gap-2">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    step="0.01" 
                                                    wire:model.live="discount_value" 
                                                    placeholder="Enter amount" 
                                                    class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <button 
                                                    wire:click="recalculate" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium transition whitespace-nowrap">
                                                    Apply
                                                </button>
                                            </div>
                                            <button 
                                                wire:click="resetDiscountForm" 
                                                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded text-sm font-medium transition">
                                                Close
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- QUICK PAYMENT SECTION --}}
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wide">Quick Payment</h3>
                                    <span class="text-lg font-bold text-blue-600">₱{{ number_format($total_due, 0) }}</span>
                                </div>

                                @if(!$showPaymentForm)
                                    <button wire:click="$set('showPaymentForm', true)" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition rounded">
                                        Add Payment
                                    </button>
                                @else
                                    <div class="space-y-3">
                                        {{-- Payment Amount --}}
                                        <div>
                                            <label class="block text-xs font-medium text-blue-900 mb-1">Payment Amount (₱)</label>
                                            <input type="number" wire:model="paymentAmount" placeholder="0" min="0" step="0.01" max="{{ $total_due - collect($quickPayments)->sum('amount') }}" class="w-full px-3 py-2 border border-blue-300 text-sm focus:outline-none focus:border-blue-500 rounded bg-white">
                                            @error('paymentAmount') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                            <p class="text-xs text-blue-700 mt-1 font-medium">Remaining: ₱{{ number_format($total_due - collect($quickPayments)->sum('amount'), 0) }}</p>
                                        </div>

                                        {{-- Payment Method --}}
                                        <div>
                                            <label class="block text-xs font-medium text-blue-900 mb-1">Payment Method</label>
                                            <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-blue-300 text-sm focus:outline-none focus:border-blue-500 rounded bg-white">
                                                <option value="cash"> Cash</option>
                                                <option value="gcash"> GCash</option>
                                                <option value="bank_transfer"> Bank Transfer</option>
                                                <option value="credit_card"> Credit Card</option>
                                            </select>
                                        </div>

                                        

                                        {{-- Action Buttons --}}
                                        <div class="flex gap-2">
                                            <button wire:click="addQuickPayment" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition rounded">
                                                Add Payment
                                            </button>
                                            <button wire:click="$set('showPaymentForm', false); $set('paymentAmount', ''); $set('paymentNote', '')" class="flex-1 px-4 py-2 bg-white border border-blue-300 text-blue-700 hover:bg-blue-50 text-sm font-medium transition rounded">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Payment List --}}
                                @if(!empty($quickPayments))
                                    <div class="mt-3 pt-3 border-t border-blue-200 space-y-2">
                                        <p class="text-xs font-bold text-blue-900 uppercase">Payments Added</p>
                                        @foreach($quickPayments as $index => $payment)
                                            <div class="flex items-center justify-between bg-white p-2 rounded border border-blue-100 text-xs">
                                                <div class="flex-1">
                                                    <span class="font-medium text-gray-900">₱{{ number_format($payment['amount'], 0) }}</span>
                                                    <span class="text-gray-500 ml-2">via {{ ucfirst(str_replace('_', ' ', $payment['method'])) }}</span>
                                                </div>
                                                <button wire:click="removeQuickPayment({{ $index }})" class="text-red-600 hover:text-red-800 font-bold">✕</button>
                                            </div>
                                        @endforeach
                                        <div class="flex justify-between text-xs font-bold text-blue-900 pt-2 border-t border-blue-200">
                                            <span>Total Payments:</span>
                                            <span>₱{{ number_format(collect($quickPayments)->sum('amount'), 0) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- CHECKOUT BUTTON --}}
                            @if($customer_id && !empty($cartItems))
                                <button wire:click="saveOrder" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition text-lg mt-4">
                                    Save Order
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
        </div>
    </div>

