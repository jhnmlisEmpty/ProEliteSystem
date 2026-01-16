<div>
    <x-page-header title="Daily Report" subtitle="Sales, supply movement, and expenses for a single day" :showDate="false">
        <x-slot name="actions">
            <div class="flex items-center gap-2 print-hide">
                <label class="text-xs font-medium text-gray-600">Report Date</label>
                <input type="date" wire:model.live="reportDate" class="px-3 py-2 text-xs rounded-md bg-gray-200 border border-gray-700 text-gray-900 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                <button wire:click="$set('reportDate', '{{ now()->format('Y-m-d') }}')" class="px-3 py-2 bg-gray-300 text-gray-900 text-xs font-semibold rounded-md hover:bg-gray-400 transition">Today</button>
                <button type="button" onclick="window.print()" class="px-3 py-2 bg-blue-600 text-gray-200 text-xs font-semibold rounded-md hover:bg-white border border-gray-300 transition">Print</button>
            </div>
        </x-slot>
    </x-page-header>

    <div class="space-y-6" wire:loading.class="opacity-60" wire:target="reportDate">
        <!-- Overall snapshot -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Overall</p>
                    <h2 class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($reportDate)->format('F d, Y') }}</h2>
                </div>
                <p class="text-xs text-gray-500">Includes all accessible branches{{ $isAdmin ? ' (admin: all branches)' : '' }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-gradient-to-br from-amber-50 to-white border border-amber-100 rounded-lg p-3">
                    <p class="text-xs text-amber-700 font-semibold">Total Sales</p>
                    <p class="text-2xl font-bold text-amber-900">₱{{ number_format($overallSummary['totalSales'] ?? 0, 0) }}</p>
                    <p class="text-[11px] text-amber-600">{{ $overallSummary['orderCount'] ?? 0 }} orders</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-lg p-3">
                    <p class="text-xs text-slate-700 font-semibold">Order Items</p>
                    <p class="text-2xl font-bold text-slate-900">{{ ($overallSummary['orderItems'] ?? collect())->count() }}</p>
                    <p class="text-[11px] text-slate-600">Products, services, upholstery, VIP</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-lg p-3">
                    <p class="text-xs text-emerald-700 font-semibold">Supply Movement</p>
                    <p class="text-lg font-bold text-emerald-900">In: {{ number_format($overallSummary['supplyIn'] ?? 0, 0) }} | Out: {{ number_format($overallSummary['supplyOut'] ?? 0, 0) }}</p>
                    <p class="text-[11px] text-emerald-600">Sum of Product Logs</p>
                </div>
                <div class="bg-gradient-to-br from-rose-50 to-white border border-rose-100 rounded-lg p-3">
                    <p class="text-xs text-rose-700 font-semibold">Expenses</p>
                    <p class="text-2xl font-bold text-rose-900">₱{{ number_format($overallSummary['expenseTotal'] ?? 0, 0) }}</p>
                    <p class="text-[11px] text-rose-600">Standalone expenses only</p>
                </div>
            </div>
        </section>

        <!-- Orders & items -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Orders and Items</h3>
                <span class="text-xs text-gray-500">All orders created on this day</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Order</th>
                            <th class="px-3 py-2 text-left">Branch</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-left">Items</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($overallSummary['orders'] ?? [] as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-semibold text-gray-900">ORD-{{ $order->id }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $order->branch->name ?? 'N/A' }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $order->customer_name ?? ($order->customer->name ?? 'Walk-in') }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-gray-900">₱{{ number_format($order->total_amount, 0) }}</td>
                                <td class="px-3 py-2">
                                    <div class="space-y-1">
                                        @foreach($order->orderItems as $item)
                                            <div class="flex items-center gap-2 text-[11px]">
                                                <span class="text-gray-700 flex-1 truncate">{{ $item->item_name }}</span>
                                                <span class="text-gray-500 text-center w-12 shrink-0">x{{ $item->quantity }}</span>
                                                <span class="text-gray-900 font-semibold text-right w-24 shrink-0">₱{{ number_format($item->total_price, 0) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">No orders found for this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Supply movement and expenses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <section class="bg-white shadow rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">Supply In / Out</h3>
                    <span class="text-xs text-gray-500">Product logs on this date</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Product</th>
                                <th class="px-3 py-2 text-left">Reason</th>
                                <th class="px-3 py-2 text-left">Reference</th>
                                <th class="px-3 py-2 text-right">Change</th>
                                <th class="px-3 py-2 text-left">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($overallSummary['productLogs'] ?? [] as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $log->product->name ?? 'Unknown Product' }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $log->reason }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $log->reference_id }}</td>
                                    <td class="px-3 py-2 text-right font-semibold {{ $log->change_amount >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $log->change_amount >= 0 ? '+' : '' }}{{ number_format($log->change_amount, 0) }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-500">{{ $log->created_at->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">No supply movement logged for this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white shadow rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">Expenses</h3>
                    <span class="text-xs text-gray-500">Expense date = selected date</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Category</th>
                                <th class="px-3 py-2 text-left">Description</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                                <th class="px-3 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($overallSummary['expenses'] ?? [] as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $expense->category }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $expense->description }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $expense->notes }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-rose-700">₱{{ number_format($expense->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">No expenses recorded for this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Branch breakdown -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Per-Branch Breakdown</h3>
                <span class="text-xs text-gray-500">Separated overview by branch</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @forelse($branchSummaries as $summary)
                    <div class="border border-gray-100 rounded-lg p-3 hover:shadow-sm transition">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-gray-900">{{ $summary['branch']->name }}</h4>
                            <span class="text-[11px] text-gray-500">{{ $summary['branch']->code }}</span>
                        </div>
                        <div class="space-y-1.5 text-[13px]">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Sales</span>
                                <span class="font-bold text-gray-900">₱{{ number_format($summary['totalSales'], 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Orders</span>
                                <span class="font-semibold text-gray-900">{{ $summary['orderCount'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Supply</span>
                                <span class="font-semibold text-gray-900">In {{ number_format($summary['supplyIn'], 0) }} / Out {{ number_format($summary['supplyOut'], 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Expenses</span>
                                <span class="font-semibold text-rose-700">₱{{ number_format($summary['expenseTotal'], 0) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No branch data available.</p>
                @endforelse
            </div>
        </section>
    </div>

    <!-- Print-only styling scoped to this page to avoid touching global CSS -->
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

            .shadow,
            .shadow-md,
            .shadow-lg,
            .shadow-xl {
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
