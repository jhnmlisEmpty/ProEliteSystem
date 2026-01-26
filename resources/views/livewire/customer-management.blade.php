<div>
    <!-- Page Header -->
    <x-page-header title="Customer Management" subtitle="Manage customer records and contact details">
        <x-slot name="actions">
            <a href="/customers/create" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Add Customer
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
                           placeholder="Search by name or phone...">
                </div>
            </div>
        </div>

        @if($search)
            <div class="mt-3">
                <button wire:click="clearFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <x-heroicon-o-x-mark class="w-4 h-4 inline-block mr-1" />
                    Clear Search
                </button>
            </div>
        @endif
    </div>

    <!-- Customers Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $customer->phone }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                            <p class="truncate" title="{{ $customer->address }}">{{ $customer->address ?: '-' }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($customer->total_orders) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($customer->total_spent) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="/customers/{{ $customer->id }}" wire:navigate
                               class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                View
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <a href="/customers/{{ $customer->id }}/edit" wire:navigate
                                   class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                    Edit
                                </a>
                                <button wire:click="delete({{ $customer->id }})" 
                                        wire:confirm="Are you sure you want to delete this customer?"
                                        class="text-red-600 hover:text-red-900 font-medium">
                                    Delete
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">View Only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No customers found</p>
                            <a href="/customers/create" wire:navigate
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                                Add your first customer
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Customers Grid (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($customers as $customer)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">{{ $customer->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $customer->phone }}</p>
                    </div>
                </div>

                @if($customer->address)
                    <p class="text-sm text-gray-700 mb-2">{{ $customer->address }}</p>
                @endif

                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                    <div>
                        <span class="text-gray-500">Orders:</span>
                        <span class="font-medium text-gray-900">{{ number_format($customer->total_orders) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Spent:</span>
                        <span class="font-medium text-gray-900">₱{{ number_format($customer->total_spent) }}</span>
                    </div>
                </div>

                @if(auth()->user()->role === 'admin')
                    <div class="flex space-x-3">
                        <a href="/customers/{{ $customer->id }}" wire:navigate
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View
                        </a>
                        <a href="/customers/{{ $customer->id }}/edit" wire:navigate
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Edit
                        </a>
                        <button wire:click="delete({{ $customer->id }})" 
                                wire:confirm="Are you sure you want to delete this customer?"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Delete
                        </button>
                    </div>
                @else
                    <a href="/customers/{{ $customer->id }}" wire:navigate
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View Profile
                    </a>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-4">No customers found</p>
                <a href="/customers/create" wire:navigate
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    Add your first customer
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    @endif
</div>
