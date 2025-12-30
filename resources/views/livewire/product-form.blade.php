<div>
    <!-- Header -->
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $product ? 'Edit Product' : 'Create New Product' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $product ? 'Update the product information and inventory details' : 'Add a new product to your inventory system' }}</p>
        </div>
        <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            Cancel
        </button>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Product Image & Preview -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Product Image</label>
            
            <!-- Image Preview Box -->
            <div class="relative mb-3 border border-gray-300 rounded-md bg-gray-50 flex items-center justify-center overflow-hidden" style="height: 200px;">
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                @elseif($product && $product->image)
                    <img src="{{ Storage::url($product->image) }}" class="max-h-full max-w-full object-contain">
                @else
                    <div class="text-center">
                        <x-heroicon-o-photo class="w-16 h-16 text-gray-300 mx-auto" />
                        <p class="text-sm text-gray-400 mt-2">No image uploaded</p>
                    </div>
                @endif
            </div>
            
            <!-- File Input -->
            <div class="flex items-center gap-3">
                <input wire:model="image" type="file" accept="image/*" class="block flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-50">
                <div wire:loading wire:target="image" class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                    
                    <span>Uploading.....</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Upload product image (max 2MB)</p>
            @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Product Name -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Product Name <span class="text-red-500">*</span></label>
            <input wire:model="name" type="text" placeholder="Enter product name" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">The product's display name in the system</p>
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- SKU -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">SKU <span class="text-red-500">*</span></label>
            <input wire:model="sku" type="text" placeholder="Enter SKU code" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('sku') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Unique product identifier for inventory tracking</p>
            @error('sku') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Type & Stock (Side by Side) -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Product Type <span class="text-red-500">*</span></label>
                <select wire:model="type" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="retail">Retail</option>
                    <option value="material">Material</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Select product category</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Stock Quantity <span class="text-red-500">*</span></label>
                <input wire:model="stock_qty" type="number" step="1" min="0" placeholder="0" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('stock_qty') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">Current inventory quantity</p>
                @error('stock_qty') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Buy Price & Sell Price (Side by Side) -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Buy Price <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm text-gray-500">₱</span>
                    <input wire:model="buy_price" type="number" step="1" placeholder="0" class="block w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('buy_price') border-red-500 @enderror">
                </div>
                <p class="text-xs text-gray-500 mt-1">Product cost price</p>
                @error('buy_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Sell Price</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm text-gray-500">₱</span>
                    <input wire:model="sell_price" type="number" step="1" placeholder="0" class="block w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('sell_price') border-red-500 @enderror">
                </div>
                <p class="text-xs text-gray-500 mt-1">Product selling price</p>
                @error('sell_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Alert Limit -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Alert Limit <span class="text-red-500">*</span></label>
            <input wire:model="alert_limit" type="number" step="1" min="0" placeholder="10" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('alert_limit') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Low stock threshold for notifications</p>
            @error('alert_limit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $product ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </form>
</div>
