<div >
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
                </div>

                 <a href="{{ route('pos.create') }}" class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Order
                </a>
            </div>
        </div>

        <!-- TOP SUMMARY ROW - Today at a Glance -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Today at a Glance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Today Sales -->
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600 font-medium">Today's Sales</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($todayRevenue, 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $todayOrders }} orders</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Sum of all payments made today</p>
                </div>

                <!-- Month Sales -->
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600 font-medium">This Month</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($monthRevenue, 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $monthOrders }} orders</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Sum of all payments in current month</p>
                </div>

                <!-- Orders Today -->
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                    <p class="text-xs text-gray-600 font-medium">Orders Today</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $todayJobs }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $inProgressJobs }} in progress</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Count of orders created today</p>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
                    <p class="text-xs text-gray-600 font-medium">Pending Orders</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $pendingJobs }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Awaiting action</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Orders with status "pending"</p>
                </div>
            </div>
        </div>

        <!-- SALES AND REVENUE SECTION -->
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Sales & Revenue</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <!-- Service Revenue Today -->
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Service Revenue Today</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($serviceRevenueToday, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Revenue from service items only (today)</p>
                </div>

                <!-- Product Revenue Today -->
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Product Revenue Today</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($productRevenueToday, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Revenue from product items only (today)</p>
                </div>

                <!-- Average Transaction -->
                <div class="bg-white rounded-lg shadow p-3">
                    <p class="text-xs text-gray-600 font-medium">Avg Transaction Value</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($avgTransactionValue, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1 italic">Total revenue ÷ Total orders</p>
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
                        <div class="flex gap-1">
                            <button wire:click="updateRevenuePeriod('daily')" 
                                class="px-2 py-1 text-xs rounded {{ $revenuePeriod === 'daily' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Daily
                            </button>
                            <button wire:click="updateRevenuePeriod('weekly')" 
                                class="px-2 py-1 text-xs rounded {{ $revenuePeriod === 'weekly' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Weekly
                            </button>
                            <button wire:click="updateRevenuePeriod('monthly')" 
                                class="px-2 py-1 text-xs rounded {{ $revenuePeriod === 'monthly' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Monthly
                            </button>
                            <button wire:click="updateRevenuePeriod('yearly')" 
                                class="px-2 py-1 text-xs rounded {{ $revenuePeriod === 'yearly' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Yearly
                            </button>
                        </div>
                    </div>
                    <canvas id="revenueChart" height="80" wire:key="revenue-chart-{{ $revenuePeriod }}"></canvas>
                </div>

                <!-- Orders Distribution (1 column) -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-xs font-semibold text-gray-900 mb-2">Orders Distribution</h3>
                    <canvas id="jobsChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- INVENTORY & CUSTOMERS SECTION -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
            <!-- Inventory Section -->
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Inventory Overview</h2>
                <div class="space-y-3">
                    <!-- Inventory Value -->
                    <div class="bg-white rounded-lg shadow p-3">
                        <p class="text-xs text-gray-600 font-medium">Total Inventory Value</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">₱{{ number_format($inventoryValue, 0) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">On hand stock</p>
                        <p class="text-xs text-gray-400 mt-1 italic">Sum of (stock qty × buy price)</p>
                    </div>

                    <!-- Low Stock Alert -->
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
            </div>

            <!-- Customer Section -->
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Customer Insights</h2>
                <div class="space-y-3">
                    <!-- Customer Stats -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg shadow p-3">
                            <p class="text-xs text-gray-600 font-medium">Total Customers</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $totalCustomers }}</p>
                            <p class="text-xs text-gray-400 mt-1 italic">All registered customers</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-3">
                            <p class="text-xs text-gray-600 font-medium">New Today</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $newCustomersToday }}</p>
                            <p class="text-xs text-gray-400 mt-1 italic">Customers created today</p>
                        </div>
                    </div>

                    <!-- Top Customers -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                            <h3 class="text-xs font-semibold text-gray-900">Top Customers</h3>
                            <p class="text-xs text-gray-600 mt-0.5">Ranked by total spending (lifetime)</p>
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
            </div>
        </div>

        <!-- TOP PRODUCTS & RECENT ORDERS -->
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

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-900">Recent Orders</h3>
                </div>
                <div class="divide-y">
                    @if(count($recentOrders) > 0)
                        @foreach($recentOrders as $order)
                            <div class="px-4 py-2.5 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-0.5">
                                    <p class="text-xs font-medium text-gray-900">Order #{{ $order['id'] }}</p>
                                    <p class="text-xs font-semibold text-gray-900">₱{{ number_format($order['total_amount'], 0) }}</p>
                                </div>
                                <p class="text-xs text-gray-600">{{ $order['customer_name'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $order['created_at'] }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="px-4 py-6 text-center">
                            <p class="text-xs text-gray-500">No orders yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let revenueChart = null;
        let jobsChart = null;

        function initCharts() {
            // Revenue Chart
            const revenueData = @json($revenueChartData);
            const revenueCtx = document.getElementById('revenueChart');
            
            if (revenueCtx) {
                // Destroy existing chart if it exists
                if (revenueChart) {
                    revenueChart.destroy();
                }
                
                revenueChart = new Chart(revenueCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: revenueData.map(d => d.date),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: revenueData.map(d => d.amount),
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
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Jobs Chart
            const jobsData = @json($jobsChartData);
            const jobsCtx = document.getElementById('jobsChart');
            
            if (jobsCtx && !jobsChart) {
                jobsChart = new Chart(jobsCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: jobsData.map(d => d.status),
                        datasets: [{
                            data: jobsData.map(d => d.count),
                            backgroundColor: [
                                'rgb(234, 179, 8)',
                                'rgb(59, 130, 246)',
                                'rgb(34, 197, 94)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initCharts);
        
        // Re-initialize charts when Livewire updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(() => initCharts(), 100);
            });
        });

        // Fallback: Listen for wire:navigate or any Livewire update
        document.addEventListener('livewire:update', () => {
            setTimeout(() => initCharts(), 100);
        });
    </script>
</div>
