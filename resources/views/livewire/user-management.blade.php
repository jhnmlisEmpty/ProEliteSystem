<div>
    {{-- Header --}}
    <x-page-header title="User Management" subtitle="Manage all system users and their permissions">
        <x-slot name="actions">
            @if(!$editingId)
                <button wire:click="openUserForm" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Add User
                </button>
            @endif
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

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <x-heroicon-o-x-circle class="w-5 h-5 text-red-400" />
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Create/Edit Form --}}
    @if($showForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $editingId ? 'Edit User' : 'Create New User' }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" wire:model="userName" placeholder="Full name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('userName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" wire:model="userEmail" placeholder="user@example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('userEmail') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ !$editingId ? '*' : '(leave blank to keep current)' }}</label>
                    <input type="password" wire:model="userPassword" placeholder="Min 6 characters" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('userPassword') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password {{ !$editingId ? '*' : '' }}</label>
                    <input type="password" wire:model="userPasswordConfirm" placeholder="Confirm password" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('userPasswordConfirm') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select wire:model="userRole" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="user">User</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('userRole') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select wire:model="userBranchId" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">No Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('userBranchId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2">
                <button wire:click="saveUser" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                    {{ $editingId ? 'Update User' : 'Create User' }}
                </button>
                <button wire:click="cancel" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md font-medium">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Filters Section --}}
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
                           placeholder="Search by name or email...">
                </div>
            </div>

            <!-- Role Filter -->
            <div>
                <label for="roleFilter" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select wire:model.live="roleFilter" 
                        id="roleFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="mt-4">
            <label for="branchFilter" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select wire:model.live="branchFilter" 
                    id="branchFilter"
                    class="block w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Clear Filters -->
        @if($search || $roleFilter || $branchFilter)
            <div class="mt-3">
                <button wire:click="$set('search', ''); $set('roleFilter', ''); $set('branchFilter', '');" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <x-heroicon-o-x-mark class="w-4 h-4 inline-block mr-1" />
                    Clear Filters
                </button>
            </div>
        @endif
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100">
                                        <span class="text-sm font-semibold text-blue-800">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : ($user->role === 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->branch?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                            <button wire:click="edit({{ $user->id }})" class="text-blue-600 hover:text-blue-900">
                                Edit
                            </button>
                            @if(!$user->isAdmin() && $user->id !== auth()->id())
                                <button wire:click="delete({{ $user->id }})" 
                                        wire:confirm="Delete {{ $user->name }}?"
                                        class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 font-medium">No users found</p>
                            <p class="text-sm text-gray-400 mt-1">Create your first user to get started</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</div>
