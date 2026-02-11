<div>
    <x-page-header title="Sales Summary Report" subtitle="Period: {{ \Carbon\Carbon::parse($startDate)->format('F d') }} — {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}" :showDate="false">
        <x-slot name="actions">
            <div class="flex items-center gap-3 print-hide">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-600">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="px-3 py-2 text-xs rounded-md bg-gray-200 border border-gray-700 text-gray-900 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-600">End Date</label>
                    <input type="date" wire:model.live="endDate" class="px-3 py-2 text-xs rounded-md bg-gray-200 border border-gray-700 text-gray-900 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                </div>
                <button type="button" onclick="window.print()" class="px-3 py-2 bg-blue-600 text-gray-200 text-xs font-semibold rounded-md hover:bg-white border border-gray-300 transition">Print</button>
            </div>
        </x-slot>
    </x-page-header>

    <div class="space-y-6" wire:loading.class="opacity-60" wire:target="startDate, endDate">
        <!-- Summary Cards -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Summary Overview</p>
                    <h2 class="text-lg font-semibold text-gray-900">By Category</h2>
                </div>
                <p class="text-xs text-gray-500">Includes all accessible branches{{ $isAdmin ? ' (admin: all branches)' : '' }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Accessories -->
                <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-100 rounded-lg p-3">
                    <p class="text-xs text-blue-700 font-semibold">Accessories</p>
                    <p class="text-lg font-bold text-blue-900 mt-1">₱{{ number_format($summary['accessories']['sales'] ?? 0, 0) }}</p>
                    <div class="mt-2 space-y-1 text-[11px]">
                        <p class="text-blue-600">Sales: <span class="font-bold">₱{{ number_format($summary['accessories']['sales'] ?? 0, 0) }}</span></p>
                        <p class="text-red-600">Expenses: <span class="font-bold">₱{{ number_format($summary['accessories']['expenses'] ?? 0, 0) }}</span></p>
                        <p class="text-blue-700 mt-1 pt-1 border-t border-blue-200">Net: <span class="font-bold">₱{{ number_format($summary['accessories']['net'] ?? 0, 0) }}</span></p>
                    </div>
                </div>

                <!-- Services -->
                <div class="bg-gradient-to-br from-green-50 to-white border border-green-100 rounded-lg p-3">
                    <p class="text-xs text-green-700 font-semibold">Services</p>
                    <p class="text-lg font-bold text-green-900 mt-1">₱{{ number_format($summary['services']['sales'] ?? 0, 0) }}</p>
                    <div class="mt-2 space-y-1 text-[11px]">
                        <p class="text-green-600">Sales: <span class="font-bold">₱{{ number_format($summary['services']['sales'] ?? 0, 0) }}</span></p>
                        <p class="text-red-600">Expenses: <span class="font-bold">₱{{ number_format($summary['services']['expenses'] ?? 0, 0) }}</span></p>
                        <p class="text-green-700 mt-1 pt-1 border-t border-green-200">Net: <span class="font-bold">₱{{ number_format($summary['services']['net'] ?? 0, 0) }}</span></p>
                    </div>
                </div>

                <!-- Upholstery -->
                <div class="bg-gradient-to-br from-amber-50 to-white border border-amber-100 rounded-lg p-3">
                    <p class="text-xs text-amber-700 font-semibold">Upholstery</p>
                    <p class="text-lg font-bold text-amber-900 mt-1">₱{{ number_format($summary['upholstery']['sales'] ?? 0, 0) }}</p>
                    <div class="mt-2 space-y-1 text-[11px]">
                        <p class="text-amber-600">Sales: <span class="font-bold">₱{{ number_format($summary['upholstery']['sales'] ?? 0, 0) }}</span></p>
                        <p class="text-red-600">Expenses: <span class="font-bold">₱{{ number_format($summary['upholstery']['expenses'] ?? 0, 0) }}</span></p>
                        <p class="text-amber-700 mt-1 pt-1 border-t border-amber-200">Net: <span class="font-bold">₱{{ number_format($summary['upholstery']['net'] ?? 0, 0) }}</span></p>
                    </div>
                </div>

                <!-- VIP -->
                <div class="bg-gradient-to-br from-purple-50 to-white border border-purple-100 rounded-lg p-3">
                    <p class="text-xs text-purple-700 font-semibold">VIP</p>
                    <p class="text-lg font-bold text-purple-900 mt-1">₱{{ number_format($summary['vip']['sales'] ?? 0, 0) }}</p>
                    <div class="mt-2 space-y-1 text-[11px]">
                        <p class="text-purple-600">Sales: <span class="font-bold">₱{{ number_format($summary['vip']['sales'] ?? 0, 0) }}</span></p>
                        <p class="text-red-600">Expenses: <span class="font-bold">₱{{ number_format($summary['vip']['expenses'] ?? 0, 0) }}</span></p>
                        <p class="text-purple-700 mt-1 pt-1 border-t border-purple-200">Net: <span class="font-bold">₱{{ number_format($summary['vip']['net'] ?? 0, 0) }}</span></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Daily Breakdown Table -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Daily Sales Breakdown</h3>
                <span class="text-xs text-gray-500">Sales and expenses by date and category</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left" rowspan="2">Date</th>
                            <th class="px-3 py-2 text-center border-l border-gray-200" colspan="2">Accessories</th>
                            <th class="px-3 py-2 text-center border-l border-gray-200" colspan="2">Services</th>
                            <th class="px-3 py-2 text-center border-l border-gray-200" colspan="2">Upholstery</th>
                            <th class="px-3 py-2 text-center border-l border-gray-200" colspan="2">VIP</th>
                        </tr>
                        <tr class="bg-gray-50">
                            <th class="px-3 py-2 text-right text-blue-600">Sales</th>
                            <th class="px-3 py-2 text-right text-blue-600">Exp</th>
                            <th class="px-3 py-2 text-right text-green-600 border-l border-gray-200">Sales</th>
                            <th class="px-3 py-2 text-right text-green-600">Exp</th>
                            <th class="px-3 py-2 text-right text-amber-600 border-l border-gray-200">Sales</th>
                            <th class="px-3 py-2 text-right text-amber-600">Exp</th>
                            <th class="px-3 py-2 text-right text-purple-600 border-l border-gray-200">Sales</th>
                            <th class="px-3 py-2 text-right text-purple-600">Exp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData ?? [] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-semibold text-gray-900">{{ $row['label'] }}</td>
                                
                                <!-- Accessories -->
                                <td class="px-3 py-2 text-right text-gray-900 border-l border-gray-200">
                                    ₱{{ number_format($row['accessories']['sales'] ?? 0, 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-red-600">
                                    ₱{{ number_format($row['accessories']['expenses'] ?? 0, 0) }}
                                </td>
                                
                                <!-- Services -->
                                <td class="px-3 py-2 text-right text-gray-900 border-l border-gray-200">
                                    ₱{{ number_format($row['services']['sales'] ?? 0, 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-red-600">
                                    ₱{{ number_format($row['services']['expenses'] ?? 0, 0) }}
                                </td>
                                
                                <!-- Upholstery -->
                                <td class="px-3 py-2 text-right text-gray-900 border-l border-gray-200">
                                    ₱{{ number_format($row['upholstery']['sales'] ?? 0, 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-red-600">
                                    ₱{{ number_format($row['upholstery']['expenses'] ?? 0, 0) }}
                                </td>
                                
                                <!-- VIP -->
                                <td class="px-3 py-2 text-right text-gray-900 border-l border-gray-200">
                                    ₱{{ number_format($row['vip']['sales'] ?? 0, 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-red-600">
                                    ₱{{ number_format($row['vip']['expenses'] ?? 0, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-4 text-center text-gray-500">No data available for the selected date range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-100 text-gray-900 font-semibold border-t-2 border-gray-300">
                        <tr>
                            <td class="px-3 py-2">SUB TOTAL</td>
                            
                            <!-- Accessories -->
                            <td class="px-3 py-2 text-right border-l border-gray-300">
                                ₱{{ number_format($summary['accessories']['sales'] ?? 0, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-red-700">
                                ₱{{ number_format($summary['accessories']['expenses'] ?? 0, 0) }}
                            </td>
                            
                            <!-- Services -->
                            <td class="px-3 py-2 text-right border-l border-gray-300">
                                ₱{{ number_format($summary['services']['sales'] ?? 0, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-red-700">
                                ₱{{ number_format($summary['services']['expenses'] ?? 0, 0) }}
                            </td>
                            
                            <!-- Upholstery -->
                            <td class="px-3 py-2 text-right border-l border-gray-300">
                                ₱{{ number_format($summary['upholstery']['sales'] ?? 0, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-red-700">
                                ₱{{ number_format($summary['upholstery']['expenses'] ?? 0, 0) }}
                            </td>
                            
                            <!-- VIP -->
                            <td class="px-3 py-2 text-right border-l border-gray-300">
                                ₱{{ number_format($summary['vip']['sales'] ?? 0, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-red-700">
                                ₱{{ number_format($summary['vip']['expenses'] ?? 0, 0) }}
                            </td>
                        </tr>
                        <tr class="bg-gradient-to-r from-red-50 to-red-100 border-t border-red-200">
                            <td class="px-3 py-2 text-red-700 font-bold">SUB NET INCOME</td>
                            
                            <!-- Accessories -->
                            <td class="px-3 py-2 text-right font-bold text-red-900 border-l border-red-200" colspan="2">
                                ₱{{ number_format($summary['accessories']['net'] ?? 0, 0) }}
                            </td>
                            
                            <!-- Services -->
                            <td class="px-3 py-2 text-right font-bold text-red-900 border-l border-red-200" colspan="2">
                                ₱{{ number_format($summary['services']['net'] ?? 0, 0) }}
                            </td>
                            
                            <!-- Upholstery -->
                            <td class="px-3 py-2 text-right font-bold text-red-900 border-l border-red-200" colspan="2">
                                ₱{{ number_format($summary['upholstery']['net'] ?? 0, 0) }}
                            </td>
                            
                            <!-- VIP -->
                            <td class="px-3 py-2 text-right font-bold text-red-900 border-l border-red-200" colspan="2">
                                ₱{{ number_format($summary['vip']['net'] ?? 0, 0) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- Grand Total Summary -->
        <section class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Grand Total</h3>
                <span class="text-xs text-gray-500">Overall financial summary</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Total Sales -->
                <div class="bg-gradient-to-br from-green-50 to-white border border-green-100 rounded-lg p-3">
                    <p class="text-xs text-green-700 font-semibold">Total Sales</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">
                        ₱{{ number_format(
                            ($summary['accessories']['sales'] ?? 0) +
                            ($summary['services']['sales'] ?? 0) +
                            ($summary['upholstery']['sales'] ?? 0) +
                            ($summary['vip']['sales'] ?? 0),
                            0
                        ) }}
                    </p>
                </div>

                <!-- Total Expenses -->
                <div class="bg-gradient-to-br from-rose-50 to-white border border-rose-100 rounded-lg p-3">
                    <p class="text-xs text-rose-700 font-semibold">Total Expenses</p>
                    <p class="text-2xl font-bold text-rose-900 mt-1">
                        ₱{{ number_format(
                            ($summary['accessories']['expenses'] ?? 0) +
                            ($summary['services']['expenses'] ?? 0) +
                            ($summary['upholstery']['expenses'] ?? 0) +
                            ($summary['vip']['expenses'] ?? 0),
                            0
                        ) }}
                    </p>
                </div>

                <!-- Net Income -->
                <div class="bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-lg p-3">
                    <p class="text-xs text-emerald-700 font-semibold">Net Income</p>
                    <p class="text-2xl font-bold text-emerald-900 mt-1">
                        ₱{{ number_format(
                            ($summary['accessories']['net'] ?? 0) +
                            ($summary['services']['net'] ?? 0) +
                            ($summary['upholstery']['net'] ?? 0) +
                            ($summary['vip']['net'] ?? 0),
                            0
                        ) }}
                    </p>
                </div>
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

            .hover\:shadow-md {
                box-shadow: none !important;
            }
        }
    </style>
</div>

