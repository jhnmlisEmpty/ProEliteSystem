<div>
    <!-- Header -->
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $customer ? 'Edit Customer' : 'Create New Customer' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $customer ? 'Update customer information and contact details' : 'Add a new customer to your system' }}</p>
        </div>
        <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            Cancel
        </button>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Customer Name <span class="text-red-500">*</span></label>
            <input wire:model="name" type="text" placeholder="Enter customer name" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Full name of the customer</p>
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Phone <span class="text-red-500">*</span></label>
            <input wire:model="phone" type="text" placeholder="Enter phone number" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Primary contact number</p>
            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Address <span class="text-red-500">*</span></label>
            <textarea wire:model="address" rows="3" placeholder="Enter street address" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"></textarea>
            <p class="text-xs text-gray-500 mt-1">Street and city information</p>
            @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $customer ? 'Update Customer' : 'Create Customer' }}
            </button>
        </div>
    </form>
</div>
