<div>
    <!-- Page Header -->
    <x-page-header title="Service Management" subtitle="Manage services and labor costs">
        <x-slot name="actions">
            <a href="/services/create" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Add Service
            </a>
        </x-slot>
    </x-page-header>

    <!-- Search Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" 
                           type="text" 
                           id="search"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Search by service name...">
                </div>
            </div>

            <!-- Branch Filter -->
            @if($canFilterBranch)
                <div class="flex-1 md:flex-none md:w-48">
                    <label for="branchFilter" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select wire:model.live="branchFilter" 
                            id="branchFilter"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- Clear Filters -->
        @if($search || $branchFilter)
            <div class="mt-3">
                <button wire:click="clearFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <x-heroicon-o-x-mark class="w-4 h-4 inline-block mr-1" />
                    Clear Filters
                </button>
            </div>
        @endif
    </div>

    <!-- Services Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Labor Cost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($services as $service)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">
                                {{ $service->branch?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₱{{ number_format($service->base_labor_cost) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $service->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="/services/{{ $service->id }}/edit" wire:navigate
                               class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                Edit
                            </a>
                            <button wire:click="delete({{ $service->id }})" 
                                    wire:confirm="Are you sure you want to delete this service?"
                                    class="text-red-600 hover:text-red-900 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <x-heroicon-o-wrench-screwdriver class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No services found</p>
                            <a href="/services/create" wire:navigate
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                                Create your first service
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Services Grid (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($services as $service)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">{{ $service->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $service->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="text-gray-500 text-sm">Labor Cost:</span>
                    <span class="text-gray-900 font-semibold ml-2">₱{{ number_format($service->base_labor_cost) }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-gray-500 text-sm">Branch:</span>
                    <span class="inline-block ml-2 px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">
                        {{ $service->branch?->name ?? 'N/A' }}
                    </span>
                </div>

                <div class="flex space-x-3">
                    <a href="/services/{{ $service->id }}/edit" wire:navigate
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Edit
                    </a>
                    <button wire:click="delete({{ $service->id }})" 
                            wire:confirm="Are you sure you want to delete this service?"
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-wrench-screwdriver class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-4">No services found</p>
                <a href="/services/create" wire:navigate
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    Create your first service
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($services->hasPages())
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif
</div>
