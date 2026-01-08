<div>
    {{-- Header --}}
    <x-page-header title="Branch Management" subtitle="Manage company branches and locations">
        <x-slot name="actions">
            <div class="text-right">
                <p class="text-sm text-gray-600">Total Branches:</p>
                <p class="text-2xl font-bold text-blue-600">{{ $branches->total() }}</p>
            </div>
        </x-slot>
    </x-page-header>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Create/Edit Form --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ $editingId ? 'Edit Branch' : 'Create New Branch' }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                <input type="text" wire:model="name" placeholder="e.g., Main Branch" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code *</label>
                <input type="text" wire:model="code" placeholder="e.g., MAIN" class="w-full px-3 py-2 border border-gray-300 rounded-md uppercase">
                @error('code') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" wire:model="phone" placeholder="+63 912 345 6789" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="flex items-center gap-2 pt-7">
                    <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea wire:model="address" rows="2" placeholder="Complete address" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                @error('address') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex gap-2">
            @if($editingId)
                <button wire:click="update" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                    Update Branch
                </button>
                <button wire:click="cancel" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md font-medium">
                    Cancel
                </button>
            @else
                <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                    Create Branch
                </button>
            @endif
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <input type="text" wire:model.live="search" placeholder="Search branches by name or code..." class="w-full px-4 py-2 border border-gray-300 rounded-md">
    </div>

    {{-- Branches List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($branches as $branch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $branch->name }}</p>
                                <p class="text-sm text-gray-500">{{ $branch->address }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $branch->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $branch->phone ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-600 space-y-1">
                                <p>👥 {{ $branch->users_count }} users</p>
                                <p>📦 {{ $branch->products_count }} products</p>
                                <p>👤 {{ $branch->customers_count }} customers</p>
                                <p>📋 {{ $branch->orders_count }} orders</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleStatus({{ $branch->id }})" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="edit({{ $branch->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                Edit
                            </button>
                            <button wire:click="delete({{ $branch->id }})" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="font-medium">No branches found</p>
                            <p class="text-sm">Create your first branch to get started</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-3 bg-gray-50">
            {{ $branches->links() }}
        </div>
    </div>
</div>
