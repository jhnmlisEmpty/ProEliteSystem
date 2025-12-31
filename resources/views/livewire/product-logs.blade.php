<div>
    <!-- Header -->
    <x-page-header title="Product Logs" :subtitle="'Viewing history for ' . $product->name . ' (SKU: ' . $product->sku . ')'">
        <x-slot name="actions">
            <button wire:click="back" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to Products
            </button>
        </x-slot>
    </x-page-header>

    <!-- Product Summary Card -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Product Snapshot</h2>
                <p class="text-sm text-gray-600">Quick view of current stock and thresholds.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-6 text-sm">
            <div>
                <span class="text-gray-500">Current Stock:</span>
                <span class="font-semibold text-gray-900 ml-2">{{ number_format($product->stock_qty) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Alert Limit:</span>
                <span class="font-semibold text-gray-900 ml-2">{{ number_format($product->alert_limit) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Type:</span>
                <span class="font-semibold text-gray-900 ml-2 capitalize">{{ $product->type }}</span>
            </div>
            @if($product->isLowStock())
                <div class="flex items-center text-red-600">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1" />
                    <span class="font-semibold">Low Stock Alert</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Logs Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference ID</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($log->change_amount > 0)
                                <span class="text-green-600">+{{ number_format($log->change_amount) }}</span>
                            @else
                                <span class="text-red-600">{{ number_format($log->change_amount) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $log->reason }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->reference_id ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500">No logs found for this product</p>
                            <p class="text-sm text-gray-400 mt-1">Stock adjustments will appear here</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Logs List (Mobile) -->
    <div class="md:hidden space-y-4">
        @forelse($logs as $log)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-sm font-medium">
                        @if($log->change_amount > 0)
                            <span class="text-green-600">+{{ number_format($log->change_amount) }}</span>
                        @else
                            <span class="text-red-600">{{ number_format($log->change_amount) }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $log->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-500">Reason:</span>
                        <span class="text-gray-900 ml-1">{{ $log->reason }}</span>
                    </div>
                    @if($log->reference_id)
                        <div>
                            <span class="text-gray-500">Reference:</span>
                            <span class="text-gray-900 ml-1">{{ $log->reference_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 mb-1">No logs found for this product</p>
                <p class="text-sm text-gray-400">Stock adjustments will appear here</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
</div>
