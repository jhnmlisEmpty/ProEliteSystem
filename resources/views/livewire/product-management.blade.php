<div>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
                <p class="mt-2 text-sm text-gray-600">Manage your retail and material products inventory</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="/products/create" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Add Product
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" 
                           type="text" 
                           id="search"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Search by name or SKU...">
                </div>
            </div>

            <!-- Type Filter -->
            <div>
                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select wire:model.live="typeFilter" 
                        id="typeFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="retail">Retail</option>
                    <option value="material">Material</option>
                </select>
            </div>
        </div>

        <!-- Clear Filters -->
        @if($search || $typeFilter)
            <div class="mt-3">
                <button wire:click="clearFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <x-heroicon-o-x-mark class="w-4 h-4 inline-block mr-1" />
                    Clear Filters
                </button>
            </div>
        @endif
    </div>

    <!-- Products Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alert Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buy Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sell Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            @if($product->isLowStock())
                                <div class="flex items-center text-xs text-red-600 mt-1">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1" />
                                    Low Stock
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 font-mono">{{ $product->sku }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->type === 'retail')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Retail
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Material
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($product->stock_qty) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($product->alert_limit) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₱{{ number_format($product->buy_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($product->sell_price)
                                ₱{{ number_format($product->sell_price) }}
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="/products/{{ $product->id }}/logs" wire:navigate
                               class="text-purple-600 hover:text-purple-800 mr-3 font-medium">
                                Logs
                            </a>
                            <a href="/products/{{ $product->id }}/adjust" wire:navigate
                               class="text-amber-600 hover:text-amber-800 mr-3 font-medium">
                                Adjust
                            </a>
                            <a href="/products/{{ $product->id }}/edit" wire:navigate
                               class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                Edit
                            </a>
                            <button wire:click="delete({{ $product->id }})" 
                                    wire:confirm="Are you sure you want to delete this product?"
                                    class="text-red-600 hover:text-red-900 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <x-heroicon-o-cube class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No products found</p>
                            <a href="/products/create" wire:navigate
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                                Create your first product
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Products Grid (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-sm p-4 {{ $product->isLowStock() ? 'border-l-4 border-red-500' : '' }}">
                <div class="flex items-start space-x-4">
                    <!-- Image -->
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-16 h-16 rounded-lg object-cover">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-photo class="w-8 h-8 text-gray-400" />
                        </div>
                    @endif

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono mt-1">{{ $product->sku }}</p>
                            </div>
                            @if($product->type === 'retail')
                                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 whitespace-nowrap">
                                    Retail
                                </span>
                            @else
                                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 whitespace-nowrap">
                                    Material
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Stock:</span>
                                <span class="font-medium text-gray-900">{{ number_format($product->stock_qty) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Alert:</span>
                                <span class="font-medium text-gray-900">{{ number_format($product->alert_limit) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Buy:</span>
                                <span class="font-medium text-gray-900">₱{{ number_format($product->buy_price) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Sell:</span>
                                <span class="font-medium text-gray-900">
                                    @if($product->sell_price)
                                        ₱{{ number_format($product->sell_price) }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($product->isLowStock())
                            <div class="mt-2 flex items-center text-xs text-red-600">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1" />
                                Low Stock Alert
                            </div>
                        @endif

                        <div class="mt-3 flex space-x-3">
                            <a href="/products/{{ $product->id }}/logs" wire:navigate
                               class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                Logs
                            </a>
                            <a href="/products/{{ $product->id }}/adjust" wire:navigate
                               class="text-sm text-amber-600 hover:text-amber-800 font-medium">
                                Adjust
                            </a>
                            <a href="/products/{{ $product->id }}/edit" wire:navigate
                               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Edit
                            </a>
                            <button wire:click="delete({{ $product->id }})" 
                                    wire:confirm="Are you sure you want to delete this product?"
                                    class="text-sm text-red-600 hover:text-red-800 font-medium">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-cube class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-4">No products found</p>
                <a href="/products/create" wire:navigate
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    Create your first product
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
