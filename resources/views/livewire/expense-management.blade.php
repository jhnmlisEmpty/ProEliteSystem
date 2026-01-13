<div>
    <!-- Page Header -->
    <x-page-header title="Expense Management" subtitle="Track and manage business expenses by category and date">
        <x-slot name="actions">
            <a href="{{ route('expenses.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Add Expense
            </a>
        </x-slot>
    </x-page-header>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-400" />
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Summary Card --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Expenses (Filtered)</p>
                <p class="text-4xl font-bold text-red-600 mt-2">₱{{ number_format($totalAmount, 2) }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg">
                <x-heroicon-o-banknotes class="w-10 h-10 text-red-600" />
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.500ms="search" 
                           id="search"
                           placeholder="Description, category, notes..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select wire:model.live="categoryFilter" 
                        id="categoryFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-2 md:col-span-2 md:grid-cols-2">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" 
                           wire:model.live="startDate" 
                           id="startDate"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" 
                           wire:model.live="endDate" 
                           id="endDate"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ $expense->expense_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <p class="text-gray-900 font-medium">{{ $expense->description }}</p>
                            @if($expense->notes)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($expense->notes, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($expense->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst(str_replace('_', ' ', $expense->category)) }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $expense->branch->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                            ₱{{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if(auth()->user()->role === 'admin')
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('expenses.edit', $expense->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition"
                                       title="Edit">
                                        <x-heroicon-o-pencil class="w-5 h-5" />
                                    </a>
                                    <button wire:click="confirmDelete({{ $expense->id }})" 
                                            class="text-red-600 hover:text-red-900 transition"
                                            title="Delete">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">View Only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 mb-4" />
                                <p class="text-gray-500 font-medium">No expenses found</p>
                                <p class="text-xs text-gray-400 mt-1">Start by adding a new expense</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $expenses->links() }}
        </div>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden space-y-4">
        @forelse($expenses as $expense)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $expense->description }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $expense->expense_date->format('M d, Y') }}</p>
                    </div>
                    <p class="text-lg font-bold text-red-600">₱{{ number_format($expense->amount, 2) }}</p>
                </div>

                <div class="flex items-center gap-2 mb-3">
                    @if($expense->category)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $expense->category)) }}
                        </span>
                    @endif
                    <span class="text-xs text-gray-500">{{ $expense->branch->name }}</span>
                </div>

                @if($expense->notes)
                    <p class="text-xs text-gray-600 mb-3">{{ $expense->notes }}</p>
                @endif

                @if(auth()->user()->role === 'admin')
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200">
                        <a href="{{ route('expenses.edit', $expense->id) }}" 
                           class="text-blue-600 hover:text-blue-900 transition">
                            <x-heroicon-o-pencil class="w-5 h-5" />
                        </a>
                        <button wire:click="confirmDelete({{ $expense->id }})" 
                                class="text-red-600 hover:text-red-900 transition">
                            <x-heroicon-o-trash class="w-5 h-5" />
                        </button>
                    </div>
                @else
                    <div class="pt-3 border-t border-gray-200 text-center">
                        <span class="text-gray-400 text-sm">View Only</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <p class="text-gray-500 font-medium">No expenses found</p>
                <p class="text-xs text-gray-400 mt-1">Start by adding a new expense</p>
            </div>
        @endforelse

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            {{ $expenses->links() }}
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($confirmingDeletion)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-red-100 p-3 rounded-full">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600" />
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete Expense</h3>
                <p class="text-sm text-gray-600 text-center mb-6">Are you sure you want to delete this expense? This action cannot be undone.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="deleteExpense" 
                            class="px-4 py-2 bg-red-600 border border-red-600 rounded-md text-sm font-medium text-white hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
