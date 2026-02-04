<div >
    <div class="max-w-7xl mx-auto">
        <x-page-header title="Dashboard" subtitle="Overview of today and recent performance" :showDate="true">
            <x-slot name="actions">
                <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Order
                </a>
            </x-slot>
        </x-page-header>

        @if($canSelectBranch)
        <!-- FILTERS -->
        <div class="mb-4 bg-white rounded-lg shadow p-4">
            <div class="flex flex-wrap lg:flex-nowrap items-end gap-3">
                <div class="flex flex-wrap lg:flex-nowrap items-end gap-3 w-full">
                    @if($canSelectBranch)
                    <div class="flex flex-wrap gap-2 items-end">
                        <span class="block text-xs font-medium text-gray-700 mb-1 w-full">Branch</span>
                        <button 
                            wire:click="switchBranch('all')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !$branchId ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All Branches
                        </button>
                        @foreach($branches as $branch)
                        <button 
                            wire:click="switchBranch({{ $branch->id }})" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $branchId == $branch->id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $branch->name }}
                        </button>
                        @endforeach
                    </div>
                    @endif

                    <div class="flex items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                            <input type="date" wire:model="start_date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                            <input type="date" wire:model="end_date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <button wire:click="applyFilters" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Filters persist in database. Select date range and click Apply.</p>
        </div>

        <!-- REPORTS SECTION -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <a 
                href="{{ route('reports.daily') }}" 
                class="bg-white rounded-lg shadow p-4 hover:shadow-lg hover:border-green-700 transition border-l-4 border-green-500"
                target="_blank" rel="noopener"
            >
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Daily Report</h3>
                        <p class="text-xs text-gray-500">Sales, supply movement, and expenses</p>
                    </div>
                </div>
            </a>
            <a 
                href="{{ route('reports.inventory') }}" 
                class="bg-white rounded-lg shadow p-4 hover:shadow-lg hover:border-emerald-700 transition border-l-4 border-emerald-500"
                target="_blank" rel="noopener"
            >
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Inventory Report</h3>
                        <p class="text-xs text-gray-500">Available and sold out items</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- ADMIN-ONLY SECTIONS -->
        <!-- SALES SUMMARY (RANGE) -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Sales Summary (Filtered Range)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-amber-500">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Today's Sales</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($todaySales, 0) }}</p>
                            <p class="text-xs text-gray-400 mt-1 italic">Based on Downpayment/Full Payment</p>
                        </div>
                        @if(isset($todayPaymentBreakdown) && is_array($todayPaymentBreakdown) && count($todayPaymentBreakdown) > 0)
                            <div class="ml-4 flex flex-col items-end min-w-[120px]">
                                @foreach($todayPaymentBreakdown as $method => $amount)
                                    <div class="text-xs text-gray-700 whitespace-nowrap">
                                        <span class="font-semibold capitalize">{{ $method }}:</span>
                                        <span>{{ $amount > 0 ? number_format($amount, 2) : '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600 font-medium">Expenses</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalBusinessExpenses, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Total business expenses</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600 font-medium">Net Sales (After Expenses)</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($finalNetSales, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">₱{{ number_format($netSales, 0) }} - ₱{{ number_format($totalBusinessExpenses, 0) }} = ₱{{ number_format($finalNetSales, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                    <p class="text-xs text-gray-600 font-medium">Inventory Value</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($inventoryValue, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Stock × buy price</p>
                </div>
            </div>
        </div>

        <!-- SALES BREAKDOWN & EXPENSES -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Product Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalProductSales, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Service Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalServiceSales, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total VIP Packages Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalVipSales, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Upholstery Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalUpholsterySales, 0) }}</p>
                </div>
            </div>
        </div>

        

        <!-- REVENUE CHART & JOBS DISTRIBUTION -->
        <div class="mb-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                <!-- Revenue Chart (3 columns) -->
                <div class="lg:col-span-3 bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-semibold text-gray-900">Revenue Trend</h3>
                    </div>
                    <canvas id="revenueChart" height="80" wire:key="revenue-chart-{{ $start_date }}-{{ $end_date }}"></canvas>
                </div>

                <!-- Orders Distribution (1 column) -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-xs font-semibold text-gray-900 mb-2">Order Status Distribution</h3>
                    <canvas id="ordersChart" height="80"></canvas>
                </div>
            </div>
        </div>
        @endif

        <!-- DAILY SALES SUMMARY (FOR NON-ADMIN) -->
        @if(!$canSelectBranch)
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Daily Sales Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Today's Sales</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($todaySales, 0) }}</p>
                            <p class="text-xs text-gray-400 mt-1 italic">Based on Downpayment/Full Payment</p>
                        </div>
                        @if(isset($todayPaymentBreakdown) && is_array($todayPaymentBreakdown) && count($todayPaymentBreakdown) > 0)
                            <div class="ml-4 flex flex-col items-end min-w-[120px]">
                                @foreach($todayPaymentBreakdown as $method => $amount)
                                    <div class="text-xs text-gray-700 whitespace-nowrap">
                                        <span class="font-semibold capitalize">{{ $method }}:</span>
                                        <span>{{ $amount > 0 ? number_format($amount, 2) : '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
                    <p class="text-xs text-gray-600 font-medium">Today's Expenses</p>
                    <p class="text-xl font-bold text-red-600 mt-0.5">₱{{ number_format($todayExpenses, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Business expenses today</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600 font-medium">Today's Net Sales</p>
                    <p class="text-xl font-bold text-green-600 mt-0.5">₱{{ number_format($todayNetSales, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Sales - Expenses</p>
                </div>
            </div>
        </div>


        <!-- TODAY'S PAYMENTS TABLE -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Today's Payments</h2>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if(count($todaysPayments) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Items</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Amount</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($todaysPayments as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">#{{ $payment['order_id'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $payment['order_branch'] ?? ($payment['order']?->branch->name ?? 'N/A') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $payment['customer_name'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $payment['order_status'] ?? ($payment['order']?->status ?? 'N/A'))) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs">
                                            <div class="space-y-1">
                                                @foreach(($payment['order_items'] ?? []) as $item)
                                                    <div class="flex items-center gap-2 text-[11px]">
                                                        <span class="text-gray-700 flex-1 truncate">{{ $item['item_name'] }}</span>
                                                        <span class="text-gray-500 text-center w-12 shrink-0">x{{ $item['quantity'] }}</span>
                                                        <span class="text-gray-900 font-semibold text-right w-16 shrink-0">₱{{ number_format($item['total_price'], 0) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-gray-900">₱{{ number_format($payment['amount'], 0) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $payment['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $payment['payment_status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $payment['payment_status'] === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($payment['payment_status']) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-gray-900">₱{{ number_format($payment['balance'], 0) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-green-700">₱{{ number_format($payment['paid_amount'], 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-8 text-center">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">No payments recorded today</p>
                    </div>
                @endif
            </div>
        </div

        <!-- TODAY'S EXPENSES TABLE -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Today's Expenses</h2>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if(count($todaysExpenses) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($todaysExpenses as $expense)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">#{{ $expense['id'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($expense['category']) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-700">{{ $expense['description'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-red-600">₱{{ number_format($expense['amount'], 0) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">{{ $expense['created_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-8 text-center">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">No expenses recorded today</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- STOCK ALERT -->
        <div class="mb-4">
            <div class="{{ $canSelectBranch ? 'grid grid-cols-1 lg:grid-cols-2 gap-3' : '' }}">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Stock Alert</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-red-50 px-3 py-2 border-b border-red-100">
                        <h3 class="text-xs font-semibold text-red-900">Low Stock Alert</h3>
                        <p class="text-xs text-red-700 mt-0.5">Products with stock < 5 units</p>
                    </div>
                    <div class="p-3">
                        @if(count($lowStockProducts) > 0)
                            <div class="space-y-1.5">
                                @foreach($lowStockProducts as $product)
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-700">{{ $product['name'] }}</span>
                                        <span class="font-semibold text-red-600">{{ $product['stock_qty'] }} units</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 text-center py-1">All stock levels good</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($canSelectBranch)
            <!-- Top Customers -->
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Top Customers</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-900">Top Customers</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Ranked by total spending (filtered range)</p>
                    </div>
                    <div class="p-3">
                        @if(count($topCustomers) > 0)
                            <div class="space-y-2">
                                @foreach($topCustomers as $customer)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-700">{{ $customer['name'] }}</span>
                                        <span class="text-xs font-semibold text-gray-900">₱{{ number_format($customer['total_spent'], 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 text-center py-1">No customer data yet</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            </div>
        </div>

        @if($canSelectBranch)
        <!-- TOP PRODUCTS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-900">Top Selling Products</h3>
                    <p class="text-xs text-gray-600 mt-0.5">Ranked by total sales revenue</p>
                </div>
                <div class="divide-y">
                    @if(count($topProducts) > 0)
                        @foreach($topProducts as $product)
                            <div class="px-4 py-2.5 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-0.5">
                                    <p class="text-xs font-medium text-gray-900">{{ $product['product_name'] }}</p>
                                    <p class="text-xs font-semibold text-gray-900">₱{{ number_format($product['total_sales'], 0) }}</p>
                                </div>
                                <p class="text-xs text-gray-600">{{ $product['quantity_sold'] }} units sold</p>
                            </div>
                        @endforeach
                    @else
                        <div class="px-4 py-6 text-center">
                            <p class="text-xs text-gray-500">No sales data yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    @if($canSelectBranch)
    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ChartManager = {
            charts: {
                revenue: null,
                orders: null
            },

            init() {
                this.initRevenueChart();
                this.initOrdersChart();
            },

            initRevenueChart() {
                const canvas = document.getElementById('revenueChart');
                if (!canvas) return;

                const data = @json($revenueChartData);
                
                // Destroy existing chart
                if (this.charts.revenue) {
                    this.charts.revenue.destroy();
                }

                this.charts.revenue = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: data.map(d => d.amount),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => '₱' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });
            },

            initOrdersChart() {
                const canvas = document.getElementById('ordersChart');
                if (!canvas) return;

                const data = @json($ordersChartData);

                if (this.charts.orders) {
                    this.charts.orders.destroy();
                }

                this.charts.orders = new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.status),
                        datasets: [{
                            data: data.map(d => d.count),
                            backgroundColor: [
                                'rgb(234, 179, 8)',
                                'rgb(59, 130, 246)',
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            },

            refresh() {
                setTimeout(() => this.init(), 100);
            }
        };

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', () => ChartManager.init());
        
        // Re-initialize charts on Livewire updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => ChartManager.refresh());
            Livewire.hook('commit', ({ component, respond }) => {
                respond(() => ChartManager.refresh());
            });
            Livewire.on('chartUpdated', () => ChartManager.refresh());
        });

        window.addEventListener('livewire:update', () => ChartManager.refresh());
    </script>
    @endif
</div>
