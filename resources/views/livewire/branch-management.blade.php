<div>
    <!-- Page Header -->
    <x-page-header title="Branch Management" subtitle="Manage branches, assigned employees, and contact details">
        <x-slot name="actions">
            <a href="/branches/create" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Create Branch
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
                           placeholder="Search by name, code or phone...">
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

    <!-- Branches Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Employees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($branches as $branch)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $branch->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $branch->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $branch->phone }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                            <p class="truncate" title="{{ $branch->address }}">{{ $branch->address }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @php
                                $assignedEmployees = $branch->employees;
                            @endphp
                            @if($assignedEmployees->count())
                                <div class="space-y-1">
                                    @foreach($assignedEmployees as $employee)
                                        <div class="text-xs text-gray-700">{{ $employee->name }}</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="/branches/{{ $branch->id }}/edit" wire:navigate
                               class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                Edit
                            </a>
                            <button wire:click="delete({{ $branch->id }})" 
                                    wire:confirm="Are you sure you want to delete this branch? This will also remove its employees."
                                    class="text-red-600 hover:text-red-900 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <x-heroicon-o-building-office-2 class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No branches found</p>
                            <a href="/branches/create" wire:navigate
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                                Create your first branch
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Branches Grid (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($branches as $branch)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">{{ $branch->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Code: {{ $branch->code }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $branch->phone }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <p class="text-sm text-gray-700 mb-2">{{ $branch->address }}</p>

                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                    <div>
                        <span class="text-gray-500">Assigned Employees:</span>
                        @php
                            $assignedEmployees = $branch->employees;
                        @endphp
                        @if($assignedEmployees->count())
                            <div class="text-xs font-medium text-gray-900 space-y-1">
                                @foreach($assignedEmployees as $employee)
                                    <div>{{ $employee->name }}</div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-500">-</span>
                        @endif
                    </div>
                </div>

                <div class="flex space-x-3">
                    <a href="/branches/{{ $branch->id }}/edit" wire:navigate
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Edit
                    </a>
                    <button wire:click="delete({{ $branch->id }})" 
                            wire:confirm="Are you sure you want to delete this branch? This will also remove its employees."
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-building-office-2 class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-4">No branches found</p>
                <a href="/branches/create" wire:navigate
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    Create your first branch
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($branches->hasPages())
        <div class="mt-6">
            {{ $branches->links() }}
        </div>
    @endif
</div>
