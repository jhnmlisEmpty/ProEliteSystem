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
            <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-end gap-3">
                @if($canSelectBranch)
                <div class="flex flex-wrap gap-2">
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

                <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                    <input type="date" wire:model="start_date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                    <input type="date" wire:model="end_date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                </div>

                <div class="flex gap-2">
                <button wire:click="applyFilters" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition w-full md:w-auto">
                    Apply Filters
                </button>
                </div>
            </div>

            <a href="{{ route('reports.daily') }}" class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Report
            </a>
            </div>
            <p class="text-xs text-gray-500 mt-2">Filters persist in database. Select date range and click Apply.</p>
        </div>

        <!-- ADMIN-ONLY SECTIONS -->
        <!-- SALES SUMMARY (RANGE) -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Sales Summary (Filtered Range)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-amber-500">
                    <p class="text-xs text-gray-600 font-medium">Today's Sales</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($todaySales, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Orders created today</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600 font-medium">Gross Sales</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($grossSales, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Sum of order gross totals</p>
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
            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Product Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalProductSales, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Service Sale</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($totalServiceSales, 0) }}</p>
                </div>
                <!-- <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Expense (Internal)</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($expenseInternal, 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Expense (Charged)</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($expenseCharged, 0) }}</p>
                </div> -->
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Total Business Expenses</p>
                    <p class="text-xl font-bold text-red-600 mt-0.5">₱{{ number_format($totalBusinessExpenses, 0) }}</p>
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
            <div class="bg-white rounded-lg shadow p-3 border-l-4 border-amber-500">
                <p class="text-xs text-gray-600 font-medium">Today's Sales</p>
                <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($todaySales, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1 italic">Total orders created today in your branch</p>
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
