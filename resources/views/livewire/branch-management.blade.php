<div>
    {{-- Header --}}
    <x-page-header title="Branch Management" subtitle="Create, manage, and organize your company branches and employees">
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

    {{-- Create/Edit Branch Form --}}
    @if($showForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $editingId ? 'Edit Branch' : 'Create New Branch' }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                    <input type="text" wire:model="name" placeholder="e.g., Main Branch" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code *</label>
                    <input type="text" wire:model="code" placeholder="e.g., MAIN" class="w-full px-3 py-2 border border-gray-300 rounded-md uppercase focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('code') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" wire:model="phone" placeholder="+63 912 345 6789" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                    <textarea wire:model="address" rows="2" placeholder="Complete address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('address') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2">
                @if($editingId)
                    <button wire:click="update" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition">
                        Update Branch
                    </button>
                    <button wire:click="cancelBranchForm" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md font-medium transition">
                        Cancel
                    </button>
                @else
                    <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition">
                        Create Branch
                    </button>
                    <button wire:click="cancelBranchForm" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md font-medium transition">
                        Cancel
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Search and Add Button --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6 flex gap-4 items-center">
        <div class="flex-1">
            <input type="text" wire:model.live="search" placeholder="Search branches by name or code..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        @if(!$showForm)
            <button wire:click="openBranchForm" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition whitespace-nowrap">
                + Add Branch
            </button>
        @endif
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Branches Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Branch Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($branches as $branch)
                            <tr class="hover:bg-gray-50 transition cursor-pointer" wire:click="selectBranch({{ $branch->id }})">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <x-heroicon-o-building-office class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900">{{ $branch->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $branch->address }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $branch->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $branch->employees_count ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click.stop="toggleStatus({{ $branch->id }})" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full transition {{ $branch->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <button wire:click.stop="edit({{ $branch->id }})" class="text-blue-600 hover:text-blue-900 transition">
                                        Edit
                                    </button>
                                    <button wire:click.stop="delete({{ $branch->id }})" onclick="return confirm('Delete this branch? This action cannot be undone.')" class="text-red-600 hover:text-red-900 transition">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <x-heroicon-o-building-office class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                                    <p class="text-gray-600 font-medium">No branches found</p>
                                    <p class="text-sm text-gray-500">Create your first branch to get started</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $branches->links() }}
                </div>
            </div>
        </div>

        <!-- Employees Panel -->
        @if($selectedBranch)
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    {{-- Branch Header --}}
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">{{ $selectedBranch->name }}</h3>
                        <p class="text-sm text-blue-100">Code: {{ $selectedBranch->code }}</p>
                    </div>

                    {{-- Employee Form --}}
                    @if($showEmployeeForm)
                        <div class="p-6 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4 text-sm">
                                {{ $editingEmployeeId ? 'Edit Employee' : 'Add Employee' }}
                            </h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Name *</label>
                                    <input type="text" wire:model="employeeName" placeholder="John Doe" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('employeeName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Position *</label>
                                    <input type="text" wire:model="employeePosition" placeholder="Mechanic" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('employeePosition') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" wire:model="employeePhone" placeholder="+63 912 345 6789" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" wire:model="employeeEmail" placeholder="john@example.com" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="flex gap-2 mt-4">
                                @if($editingEmployeeId)
                                    <button wire:click="updateEmployee" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-xs font-medium transition">
                                        Update
                                    </button>
                                @else
                                    <button wire:click="saveEmployee" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-xs font-medium transition">
                                        Add Employee
                                    </button>
                                @endif
                                <button wire:click="cancelEmployeeForm" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded-md text-xs font-medium transition">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-6 border-b border-gray-200">
                            <button wire:click="openEmployeeForm" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                                + Add Employee
                            </button>
                        </div>
                    @endif

                    {{-- Employees List --}}
                    <div class="p-6">
                        @if($selectedBranch->employees->count() > 0)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($selectedBranch->employees as $employee)
                                    <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition">
                                        <div class="flex items-start gap-3 mb-2">
                                            <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <span class="text-xs font-semibold text-green-700">{{ substr($employee->name, 0, 1) }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="text-sm font-semibold text-gray-900 truncate">{{ $employee->name }}</h5>
                                                <p class="text-xs text-gray-600">{{ $employee->position }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($employee->phone || $employee->email)
                                            <div class="text-xs text-gray-600 mb-3 space-y-1 ml-11">
                                                @if($employee->phone)
                                                    <p>{{ $employee->phone }}</p>
                                                @endif
                                                @if($employee->email)
                                                    <p class="truncate">{{ $employee->email }}</p>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="flex gap-2 ml-11">
                                            <button wire:click="editEmployee({{ $employee->id }})" class="text-xs text-blue-600 hover:text-blue-900 font-medium transition">
                                                Edit
                                            </button>
                                            <button wire:click="deleteEmployee({{ $employee->id }})" onclick="return confirm('Delete this employee?')" class="text-xs text-red-600 hover:text-red-900 font-medium transition">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <x-heroicon-o-user-group class="w-10 h-10 mx-auto mb-2 opacity-40" />
                                <p class="text-sm font-medium">No employees yet</p>
                                <p class="text-xs">Add your first employee</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
