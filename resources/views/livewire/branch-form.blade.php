<div>
    <!-- Header -->
    <x-page-header :title="$branch ? 'Edit Branch' : 'Create New Branch'" :subtitle="$branch ? 'Update branch information and assign employees' : 'Add a new branch to your system'">
        <x-slot name="actions">
            <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Cancel
            </button>
        </x-slot>
    </x-page-header>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Branch Name <span class="text-red-500">*</span></label>
            <p class="text-sm text-gray-600 mb-2">Human-readable name for this branch.</p>
            <input wire:model="name" type="text" placeholder="Enter branch name" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Code -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Branch Code</label>
            <p class="text-sm text-gray-600 mb-2">Short identifier used in references and reports.</p>
            <input wire:model="code" type="text" placeholder="Enter branch code" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror">
            @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Phone</label>
            <p class="text-sm text-gray-600 mb-2">Primary contact number for this branch.</p>
            <input wire:model="phone" type="text" placeholder="Enter phone number" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Address</label>
            <p class="text-sm text-gray-600 mb-2">Street and city address for this branch.</p>
            <textarea wire:model="address" rows="3" placeholder="Enter address" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"></textarea>
            @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Status -->
        <div class="flex items-center">
            <input id="is_active" type="checkbox" wire:model="is_active" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
        </div>

        <!-- Assign Employee Users -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Assign Employees</label>
            <p class="text-sm text-gray-600 mb-3">Click on employees (from users with role "employee") to assign or remove them from this branch.</p>
            
            <div class="space-y-4">
                <!-- Selected Employees Section -->
                <div>
                    <p class="text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Selected Employees</p>
                    <div class="flex flex-wrap gap-2 p-3 bg-blue-50 rounded-lg border border-blue-200 min-h-12">
                        @if(count($employee_ids) > 0)
                            @foreach($employeeCandidates->whereIn('id', $employee_ids) as $employee)
                                <button type="button" wire:click="toggleEmployee({{ $employee->id }})"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-full transition">
                                    <span>{{ $employee->name }}</span>
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic">No employees selected</p>
                        @endif
                    </div>
                </div>

                <!-- Available Employees Section -->
                <div>
                    <p class="text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Available Employees</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($employeeCandidates->whereNotIn('id', $employee_ids) as $candidate)
                            <button type="button" wire:click="toggleEmployee({{ $candidate->id }})"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-900 text-sm font-medium rounded-full transition">
                                <x-heroicon-o-plus class="w-4 h-4" />
                                <span>{{ $candidate->name }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if($employeeCandidates->isEmpty())
                        <p class="text-sm text-gray-500 italic">No employee users available. Create users with role "employee" first.</p>
                    @endif
                </div>
            </div>
            
            @error('employee_user_ids') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>


        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $branch ? 'Update Branch' : 'Create Branch' }}
            </button>
        </div>
    </form>
</div>
