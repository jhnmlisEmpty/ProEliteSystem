<div>
    <!-- Header -->
    <x-page-header :title="$service ? 'Edit Service' : 'Create New Service'" :subtitle="$service ? 'Update service information and labor costs' : 'Add a new service to your system'">
        <x-slot name="actions">
            <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Cancel
            </button>
        </x-slot>
    </x-page-header>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Service Name -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Service Name <span class="text-red-500">*</span></label>
            <p class="text-sm text-gray-600 mb-2">Name displayed to customers and staff.</p>
            <input wire:model="name" type="text" placeholder="Enter service name" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">The service's display name (e.g., Custom Paint, Upholstery)</p>
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Base Labor Cost -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Base Labor Cost <span class="text-red-500">*</span></label>
            <p class="text-sm text-gray-600 mb-2">Default labor rate before materials or extras.</p>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm text-gray-500">₱</span>
                <input wire:model="base_labor_cost" type="number" step="1" min="0" placeholder="0" class="block w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('base_labor_cost') border-red-500 @enderror">
            </div>
            <p class="text-xs text-gray-500 mt-1">Base labor rate for this service</p>
            @error('base_labor_cost') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Branch Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Branch <span class="text-red-500">*</span></label>
            <p class="text-sm text-gray-600 mb-2">Assign this service to a specific branch.</p>
            @if($canSelectBranch)
                <select wire:model="branch_id" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branch_id') border-red-500 @enderror">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Select the branch where this service will be available</p>
            @else
                <input type="text" value="{{ $branches->firstWhere('id', $branch_id)?->name ?? 'Unknown Branch' }}" disabled class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-100 text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Your branch is automatically assigned</p>
            @endif
            @error('branch_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $service ? 'Update Service' : 'Create Service' }}
            </button>
        </div>
    </form>
</div>
