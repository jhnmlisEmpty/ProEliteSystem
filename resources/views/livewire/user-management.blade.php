<div>
    <!-- Page Header -->
    <x-page-header title="User Management" subtitle="Manage user accounts, roles, and branch access">
        <x-slot name="actions">
            <a href="/users/create" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Create User
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
                           placeholder="Search by name or email...">
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

    <!-- Users Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'manager' => 'bg-blue-100 text-blue-800',
                                    'order_creator' => 'bg-purple-100 text-purple-800',
                                    'employee' => 'bg-green-100 text-green-800',
                                ];
                                $role = $user->role ?? 'employee';
                                $colorClass = $roleColors[$role] ?? 'bg-gray-100 text-gray-800';
                                $roleDisplay = ucwords(str_replace('_', ' ', $role));
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ $roleDisplay }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($user->branch)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ $user->branch->name }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($user->role === 'employee')
                                <a href="/employees/{{ $user->id }}/services" wire:navigate
                                   class="text-green-600 hover:text-green-900 mr-3 font-medium">
                                    Services
                                </a>
                                <a href="/employees/{{ $user->id }}/upholstery" wire:navigate
                                   class="text-purple-600 hover:text-purple-900 mr-3 font-medium">
                                    Upholstery
                                </a>
                            @endif
                            <a href="/users/{{ $user->id }}/edit" wire:navigate
                               class="text-blue-600 hover:text-blue-900 mr-3 font-medium">
                                Edit
                            </a>
                            <button wire:click="delete({{ $user->id }})" 
                                    wire:confirm="Are you sure you want to delete this user?"
                                    class="text-red-600 hover:text-red-900 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No users found</p>
                            <a href="/users/create" wire:navigate
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                                Create your first user
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Users Grid (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($users as $user)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">{{ $user->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                    <div>
                        <span class="text-gray-500 block mb-1">Role:</span>
                        @php
                            $roleColors = [
                                'admin' => 'bg-red-100 text-red-800',
                                'manager' => 'bg-blue-100 text-blue-800',
                                'order_creator' => 'bg-purple-100 text-purple-800',
                                'employee' => 'bg-green-100 text-green-800',
                            ];
                            $role = $user->role ?? 'employee';
                            $colorClass = $roleColors[$role] ?? 'bg-gray-100 text-gray-800';
                            $roleDisplay = ucwords(str_replace('_', ' ', $role));
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                            {{ $roleDisplay }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Branch:</span>
                        @if($user->branch)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                {{ $user->branch->name }}
                            </span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">-</span>
                        @endif
                    </div>
                </div>

                <div class="flex space-x-3">
                    <a href="/users/{{ $user->id }}/edit" wire:navigate
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Edit
                    </a>
                    <button wire:click="delete({{ $user->id }})" 
                            wire:confirm="Are you sure you want to delete this user?"
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-4">No users found</p>
                <a href="/users/create" wire:navigate
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    Create your first user
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</div>
