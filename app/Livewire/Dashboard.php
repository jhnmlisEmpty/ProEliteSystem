<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\JobOrder;
use App\Models\Payment;
use Carbon\Carbon;

class Dashboard extends Component
{
    // Revenue metrics
    public $totalRevenue = 0;
    public $todayRevenue = 0;
    public $monthRevenue = 0;
    public $serviceRevenueToday = 0;
    public $productRevenueToday = 0;
    public $avgTransactionValue = 0;
    
    // Order metrics
    public $totalOrders = 0;
    public $todayOrders = 0;
    public $monthOrders = 0;
    
    // Job metrics
    public $pendingJobs = 0;
    public $inProgressJobs = 0;
    public $completedJobs = 0;
    public $todayJobs = 0;
    
    // Customer metrics
    public $totalCustomers = 0;
    public $newCustomersToday = 0;
    public $topCustomers = [];
    
    // Inventory metrics
    public $lowStockProducts = [];
    public $inventoryValue = 0;
    public $topProducts = [];
    
    // Other
    public $totalServices = 0;
    public $recentOrders = [];
    public $revenueChartData = [];
    public $jobsChartData = [];
    public $revenuePeriod = 'weekly'; // daily, weekly, monthly, yearly

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // === REVENUE METRICS ===
        // Total revenue from all payments
        $this->totalRevenue = Payment::sum('amount') ?? 0;
        
        // Today's revenue
        $this->todayRevenue = Payment::whereDate('created_at', Carbon::today())->sum('amount') ?? 0;
        
        // This month's revenue
        $this->monthRevenue = Payment::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount') ?? 0;
        
        // Service revenue today
        $this->serviceRevenueToday = OrderItem::whereHas('order', function($q) {
                $q->whereDate('created_at', Carbon::today());
            })
            ->whereNotNull('service_id')
            ->sum('total_price') ?? 0;
        
        // Product revenue today
        $this->productRevenueToday = OrderItem::whereHas('order', function($q) {
                $q->whereDate('created_at', Carbon::today());
            })
            ->whereNotNull('product_id')
            ->sum('total_price') ?? 0;
        
        // Average transaction value
        $orderCount = Order::count();
        $this->avgTransactionValue = $orderCount > 0 ? ($this->totalRevenue / $orderCount) : 0;
        
        // === ORDER METRICS ===
        $this->totalOrders = Order::count();
        $this->todayOrders = Order::whereDate('created_at', Carbon::today())->count();
        $this->monthOrders = Order::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        // === JOB METRICS ===
        $this->pendingJobs = JobOrder::where('status', 'pending')->count();
        $this->inProgressJobs = JobOrder::where('status', 'in_progress')->count();
        $this->completedJobs = JobOrder::where('status', 'completed')->count();
        $this->todayJobs = JobOrder::whereDate('created_at', Carbon::today())->count();
        
        // === CUSTOMER METRICS ===
        $this->totalCustomers = \App\Models\Customer::count();
        $this->newCustomersToday = \App\Models\Customer::whereDate('created_at', Carbon::today())->count();
        
        // Top 5 customers by spend
        $this->topCustomers = Order::selectRaw('customer_id, SUM(total_amount) as total_spent')
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->with('customer')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'name' => $order->customer->name ?? 'N/A',
                    'total_spent' => $order->total_spent,
                ];
            })
            ->toArray();
        
        // === INVENTORY METRICS ===
        // Low stock products (less than 5 units)
        $this->lowStockProducts = Product::where('stock_qty', '<', 5)
            ->orderBy('stock_qty', 'asc')
            ->get()
            ->toArray();
        
        // Inventory value on hand
        $this->inventoryValue = Product::selectRaw('SUM(stock_qty * buy_price) as total_value')
            ->value('total_value') ?? 0;
        
        // Top 5 best-selling products
        $this->topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_price) as total_sales')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->with('product')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'N/A',
                    'quantity_sold' => $item->total_quantity,
                    'total_sales' => $item->total_sales,
                ];
            })
            ->toArray();
        
        // === OTHER ===
        $this->totalServices = Service::count();
        
        // Recent orders (last 5)
        $this->recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->format('M d, Y'),
                ];
            })
            ->toArray();
        
        // === CHART DATA ===
        // Revenue chart data based on selected period
        $this->revenueChartData = $this->getRevenueChartData();
        
        // Jobs status data
        $this->jobsChartData = [
            ['status' => 'Pending', 'count' => $this->pendingJobs],
            ['status' => 'In Progress', 'count' => $this->inProgressJobs],
            ['status' => 'Completed', 'count' => $this->completedJobs],
        ];
    }

    public function updateRevenuePeriod($period)
    {
        $this->revenuePeriod = $period;
        $this->revenueChartData = $this->getRevenueChartData();
    }

    private function getRevenueChartData()
    {
        switch ($this->revenuePeriod) {
            case 'daily':
                // Last 24 hours (hourly)
                return collect(range(23, 0))->map(function ($hoursAgo) {
                    $hour = Carbon::now()->subHours($hoursAgo);
                    return [
                        'date' => $hour->format('h A'),
                        'amount' => Payment::whereBetween('created_at', [
                            $hour->copy()->startOfHour(),
                            $hour->copy()->endOfHour()
                        ])->sum('amount') ?? 0
                    ];
                })->toArray();
            
            case 'weekly':
                // Last 7 days
                return collect(range(6, 0))->map(function ($daysAgo) {
                    $date = Carbon::today()->subDays($daysAgo);
                    return [
                        'date' => $date->format('M d'),
                        'amount' => Payment::whereDate('created_at', $date)->sum('amount') ?? 0
                    ];
                })->toArray();
            
            case 'monthly':
                // Last 30 days
                return collect(range(29, 0))->map(function ($daysAgo) {
                    $date = Carbon::today()->subDays($daysAgo);
                    return [
                        'date' => $date->format('M d'),
                        'amount' => Payment::whereDate('created_at', $date)->sum('amount') ?? 0
                    ];
                })->toArray();
            
            case 'yearly':
                // Last 12 months
                return collect(range(11, 0))->map(function ($monthsAgo) {
                    $date = Carbon::today()->subMonths($monthsAgo);
                    return [
                        'date' => $date->format('M Y'),
                        'amount' => Payment::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->sum('amount') ?? 0
                    ];
                })->toArray();
            
            default:
                // Default to weekly
                return collect(range(6, 0))->map(function ($daysAgo) {
                    $date = Carbon::today()->subDays($daysAgo);
                    return [
                        'date' => $date->format('M d'),
                        'amount' => Payment::whereDate('created_at', $date)->sum('amount') ?? 0
                    ];
                })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
