<div>
    <x-page-header title="Inventory Report" subtitle="Complete inventory status of all available and sold out items" :showDate="false">
        <x-slot name="actions">
            <button type="button" onclick="window.print()" class="px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700 transition print-hide">
                Print Report
            </button>
        </x-slot>
    </x-page-header>

    <div class="space-y-6">
        <!-- Overall Summary Cards -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">Inventory Summary</h2>
                <p class="text-xs text-gray-500">{{ $isAdmin ? 'All branches' : 'Current branch only' }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-lg p-3">
                    <p class="text-xs text-emerald-700 font-semibold">Available Items</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ count($availableItems) }}</p>
                    <p class="text-[11px] text-emerald-600">Total inventory value: ₱{{ number_format(collect($availableItems)->sum('inventory_value'), 0) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-50 to-white border border-rose-100 rounded-lg p-3">
                    <p class="text-xs text-rose-700 font-semibold">Sold Out Items</p>
                    <p class="text-2xl font-bold text-rose-900">{{ count($soldOutItems) }}</p>
                    <p class="text-[11px] text-rose-600">Products with no stock</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-100 rounded-lg p-3">
                    <p class="text-xs text-blue-700 font-semibold">Total Products</p>
                    <p class="text-2xl font-bold text-blue-900">{{ count($availableItems) + count($soldOutItems) }}</p>
                    <p class="text-[11px] text-blue-600">Active products in system</p>
                </div>
            </div>
        </section>

        <!-- Available Items Table -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Available Items</h3>
                <span class="text-xs text-gray-500">Products with stock on hand</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-emerald-50 text-emerald-700 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-left">SKU</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Branch</th>
                            <th class="px-3 py-2 text-right">Stock Qty</th>
                            <th class="px-3 py-2 text-right">Alert Limit</th>
                            <th class="px-3 py-2 text-right">Buy Price</th>
                            <th class="px-3 py-2 text-right">Sell Price</th>
                            <th class="px-3 py-2 text-right">Inventory Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($availableItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-900 font-medium">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-gray-700 font-mono">{{ $item['sku'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold {{ $item['type'] === 'retail' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['branch'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-emerald-700">{{ number_format($item['stock_qty']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item['alert_limit']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">₱{{ number_format($item['buy_price'], 0) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    @if($item['sell_price'])
                                        ₱{{ number_format($item['sell_price'], 0) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-emerald-900">₱{{ number_format($item['inventory_value'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-4 text-center text-gray-500">All products are sold out.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($availableItems) > 0)
                        <tfoot class="bg-emerald-50 font-bold">
                            <tr>
                                <td colspan="8" class="px-3 py-2 text-right text-gray-900">Total Inventory Value</td>
                                <td class="px-3 py-2 text-right text-emerald-900">₱{{ number_format(collect($availableItems)->sum('inventory_value'), 0) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        <!-- Sold Out Items Table -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Sold Out Items</h3>
                <span class="text-xs text-gray-500">Products with zero or negative stock</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-rose-50 text-rose-700 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-left">SKU</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Branch</th>
                            <th class="px-3 py-2 text-right">Stock Qty</th>
                            <th class="px-3 py-2 text-right">Alert Limit</th>
                            <th class="px-3 py-2 text-right">Buy Price</th>
                            <th class="px-3 py-2 text-right">Sell Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($soldOutItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-900 font-medium">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-gray-700 font-mono">{{ $item['sku'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold {{ $item['type'] === 'retail' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['branch'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-rose-700">{{ number_format($item['stock_qty']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item['alert_limit']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">₱{{ number_format($item['buy_price'], 0) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    @if($item['sell_price'])
                                        ₱{{ number_format($item['sell_price'], 0) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 text-center text-gray-500">No sold out items. All products have stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Print-only styling -->
    <style>
        @media print {
            nav,
            .sticky,
            .print-hide {
                display: none !important;
            }

            body {
                background: #fff;
            }

            main {
                padding-top: 0 !important;
            }

            .shadow {
                box-shadow: none !important;
            }

            section,
            table,
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</div>
