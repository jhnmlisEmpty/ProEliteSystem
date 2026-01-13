<div>
    <!-- Header -->
    <x-page-header :title="$user ? 'Edit User' : 'Create New User'" :subtitle="$user ? 'Update user details, roles and branch access' : 'Add a new user to your system'">
        <x-slot name="actions">
            <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Cancel
            </button>
        </x-slot>
    </x-page-header>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Name & Email (Side by Side) -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">Name <span class="text-red-500">*</span></label>
                <p class="text-sm text-gray-600 mb-2">Full name used for identification.</p>
                <input wire:model="name" type="text" placeholder="Enter full name" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">Email <span class="text-red-500">*</span></label>
                <p class="text-sm text-gray-600 mb-2">Login email and primary contact.</p>
                <input wire:model="email" type="email" placeholder="Enter email address" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Role & Branch (Side by Side) -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">Role <span class="text-red-500">*</span></label>
                <p class="text-sm text-gray-600 mb-2">Access level for system features.</p>
                <select wire:model="role" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                    @foreach($roles as $r)
                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">Branch</label>
                <p class="text-sm text-gray-600 mb-2">Assign the user to a branch (optional for admins).</p>
                <select wire:model="branch_id" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branch_id') border-red-500 @enderror">
                    <option value="">No Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">Password {{ $user ? '(optional)' : '' }} <span class="text-red-500">{{ $user ? '' : '*' }}</span></label>
            <p class="text-sm text-gray-600 mb-2">{{ $user ? 'Set only if you want to change the password.' : 'Minimum 8 characters.' }}</p>
            <input wire:model="password" type="password" placeholder="Enter password" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $user ? 'Update User' : 'Create User' }}
            </button>
        </div>
    </form>
</div>
