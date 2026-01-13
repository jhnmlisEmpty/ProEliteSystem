<div>
    <!-- Header -->
    <x-page-header 
        :title="$expenseId ? 'Edit Expense' : 'Create New Expense'" 
        :subtitle="$expenseId ? 'Update expense details and information' : 'Add a new business expense to track spending'">
        <x-slot name="actions">
            <button wire:click="cancel" type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Cancel
            </button>
        </x-slot>
    </x-page-header>

    <!-- Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        {{-- Branch Selection & Expense Date (Side by Side) --}}
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">
                    Branch <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-600 mb-2">Select the branch this expense belongs to.</p>
                <select 
                    wire:model="branch_id" 
                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ !$canSelectBranch ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    {{ !$canSelectBranch ? 'disabled' : '' }}>
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">{{ !$canSelectBranch ? 'Your branch is automatically selected' : 'Choose branch for this expense' }}</p>
                @error('branch_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">
                    Expense Date <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-600 mb-2">When this expense occurred.</p>
                <input 
                    type="date" 
                    wire:model="expense_date" 
                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">The date this expense was incurred</p>
                @error('expense_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Category & Description (Side by Side) --}}
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">
                    Category <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-600 mb-2">Categorize this expense for better tracking.</p>
                <select 
                    wire:model="category" 
                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select a category</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Choose appropriate expense category</p>
                @error('category') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-600 mb-2">Brief description of the expense.</p>
                <input 
                    type="text" 
                    wire:model="description" 
                    placeholder="e.g., Monthly electricity bill"
                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">What is this expense for?</p>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">
                Amount (₱) <span class="text-red-500">*</span>
            </label>
            <p class="text-sm text-gray-600 mb-2">Total cost of this expense.</p>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm text-gray-500">₱</span>
                <input 
                    type="number" 
                    min="0" 
                    step="1" 
                    wire:model="amount" 
                    placeholder="0"
                    class="block w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror">
            </div>
            <p class="text-xs text-gray-500 mt-1">Expense amount in Philippine Pesos</p>
            @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-1">
                Notes
            </label>
            <p class="text-sm text-gray-600 mb-2">Additional details about this expense (optional).</p>
            <textarea 
                wire:model="notes" 
                rows="3"
                placeholder="Add any additional notes or details..."
                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"></textarea>
            <p class="text-xs text-gray-500 mt-1">Add any additional details about this expense</p>
            @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Submit Button --}}
        <div class="pt-4">
            <button 
                type="submit" 
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $expenseId ? 'Update Expense' : 'Create Expense' }}
            </button>
        </div>
    </form>
</div>
