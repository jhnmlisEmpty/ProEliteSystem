<div>
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Adjust Stock</h1>
            <p class="text-sm text-gray-500 mt-1">Update inventory for {{ $product->name }} (SKU: {{ $product->sku }})</p>
        </div>
        <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <div class="flex flex-wrap gap-4 text-sm text-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Current Stock:</span>
                <span class="font-semibold">{{ number_format($product->stock_qty) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Type:</span>
                <span class="font-semibold capitalize">{{ $product->type }}</span>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Adjustment</label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" wire:model="direction" value="increase" class="text-blue-600 border-gray-300 focus:ring-blue-500">
                            Increase
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" wire:model="direction" value="decrease" class="text-blue-600 border-gray-300 focus:ring-blue-500">
                            Decrease
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Change Amount <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="change_amount" step="1" min="1" placeholder="0" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('change_amount') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Enter a whole number quantity to add or subtract</p>
                    @error('change_amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Reason <span class="text-red-500">*</span></label>
                <input type="text" wire:model="reason" placeholder="e.g., Sale, Adjustment, Damage, Return" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('reason') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">Short description for the audit trail</p>
                @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Adjustment
                </button>
                <button type="button" wire:click="cancel" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
