<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\JobOrder;
use App\Models\Filter;
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
    public $start_date;
    public $end_date;

    public function mount()
    {
        $this->loadOrCreateFilter();
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->loadRevenueMetrics();
        $this->loadOrderMetrics();
        $this->loadJobMetrics();
        $this->loadCustomerMetrics();
        $this->loadInventoryMetrics();
        $this->loadRecentOrders();
        $this->loadChartData();
    }

    private function loadOrCreateFilter()
    {
        $filter = Filter::where('filter_type', 'dashboard')->first();
        
        if ($filter) {
            $this->start_date = $filter->start_date->format('Y-m-d');
            $this->end_date = $filter->end_date->format('Y-m-d');
        } else {
            $this->setDefaultDates();
            $this->storeFilter();
        }
    }

    private function setDefaultDates()
    {
        $this->start_date = Carbon::today()->subDays(29)->format('Y-m-d');
        $this->end_date = Carbon::today()->format('Y-m-d');
    }

    private function getDateRange()
    {
        return [
            Carbon::parse($this->start_date)->startOfDay(),
            Carbon::parse($this->end_date)->endOfDay()
        ];
    }

    private function loadRevenueMetrics()
    {
        $this->totalRevenue = Payment::sum('amount') ?? 0;
        $this->todayRevenue = Payment::whereDate('created_at', Carbon::today())->sum('amount') ?? 0;
        $this->monthRevenue = Payment::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount') ?? 0;
        
        $this->serviceRevenueToday = OrderItem::whereHas('order', fn($q) => 
            $q->whereDate('created_at', Carbon::today())
        )->whereNotNull('service_id')->sum('total_price') ?? 0;
        
        $this->productRevenueToday = OrderItem::whereHas('order', fn($q) => 
            $q->whereDate('created_at', Carbon::today())
        )->whereNotNull('product_id')->sum('total_price') ?? 0;
        
        $orderCount = Order::count();
        $this->avgTransactionValue = $orderCount > 0 ? ($this->totalRevenue / $orderCount) : 0;
    }

    private function loadOrderMetrics()
    {
        $this->totalOrders = Order::count();
        $this->todayOrders = Order::whereDate('created_at', Carbon::today())->count();
        $this->monthOrders = Order::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
    }

    private function loadJobMetrics()
    {
        $this->pendingJobs = JobOrder::where('status', 'pending')->count();
        $this->inProgressJobs = JobOrder::where('status', 'in_progress')->count();
        $this->completedJobs = JobOrder::where('status', 'completed')->count();
        $this->todayJobs = JobOrder::whereDate('created_at', Carbon::today())->count();
    }

    private function loadCustomerMetrics()
    {
        $this->totalCustomers = \App\Models\Customer::count();
        $this->newCustomersToday = \App\Models\Customer::whereDate('created_at', Carbon::today())->count();
        
        $this->topCustomers = Order::selectRaw('customer_id, SUM(total_amount) as total_spent')
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->with('customer')
            ->take(5)
            ->get()
            ->map(fn($order) => [
                'name' => $order->customer->name ?? 'N/A',
                'total_spent' => $order->total_spent,
            ])
            ->toArray();
    }

    private function loadInventoryMetrics()
    {
        $this->lowStockProducts = Product::where('stock_qty', '<', 5)
            ->orderBy('stock_qty', 'asc')
            ->get()
            ->toArray();
        
        $this->inventoryValue = Product::selectRaw('SUM(stock_qty * buy_price) as total_value')
            ->value('total_value') ?? 0;
        
        $this->totalServices = Service::count();
    }

    private function loadRecentOrders()
    {
        $this->topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_price) as total_sales')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->with('product')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'product_name' => $item->product->name ?? 'N/A',
                'quantity_sold' => $item->total_quantity,
                'total_sales' => $item->total_sales,
            ])
            ->toArray();
        
        $this->recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'customer_name' => $order->customer->name ?? 'N/A',
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    private function loadChartData()
    {
        $this->revenueChartData = $this->getRevenueChartData();
        $this->jobsChartData = [
            ['status' => 'Pending', 'count' => $this->pendingJobs],
            ['status' => 'In Progress', 'count' => $this->inProgressJobs],
            ['status' => 'Completed', 'count' => $this->completedJobs],
        ];
    }

    public function updateDateRange()
    {
        $this->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        // Store the filter dates in database
        $this->storeFilter();
        
        $this->loadDashboardData();
        $this->dispatch('chartUpdated');
    }

    /**
     * Store or update the filter dates in the database
     */
    private function storeFilter()
    {
        Filter::updateOrCreate(
            ['filter_type' => 'dashboard'],
            [
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]
        );
    }

    private function getRevenueChartData()
    {
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();
        $diffInDays = $start->diffInDays($end);
        
        if ($diffInDays <= 1) {
            // Hourly grouping for 1 day or less
            $data = [];
            $current = $start->copy();
            
            for ($i = 0; $i < 24; $i++) {
                $data[] = [
                    'date' => $current->format('h A'),
                    'amount' => Payment::whereBetween('created_at', [
                        $current->copy()->startOfHour(),
                        $current->copy()->endOfHour()
                    ])->sum('amount') ?? 0
                ];
                $current->addHour();
            }
            return $data;
        } elseif ($diffInDays <= 31) {
            // Daily grouping - only show dates in range
            $data = [];
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $data[] = [
                    'date' => $current->format('M d'),
                    'amount' => Payment::whereDate('created_at', $current)->sum('amount') ?? 0
                ];
                $current->addDay();
            }
            return $data;
        } elseif ($diffInDays <= 365) {
            // Weekly grouping - only show weeks within range
            $data = [];
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->addDays(6);
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }
                
                $data[] = [
                    'date' => $current->format('M d') . ' - ' . $weekEnd->format('M d'),
                    'amount' => Payment::whereBetween('created_at', [
                        $current->copy()->startOfDay(),
                        $weekEnd->copy()->endOfDay()
                    ])->sum('amount') ?? 0
                ];
                $current->addWeek();
            }
            return $data;
        } else {
            // Monthly grouping - only show months within range
            $data = [];
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $monthEnd = $current->copy()->endOfMonth();
                if ($monthEnd->gt($end)) {
                    $monthEnd = $end->copy();
                }
                
                $data[] = [
                    'date' => $current->format('M Y'),
                    'amount' => Payment::whereBetween('created_at', [
                        $current->copy()->startOfDay(),
                        $monthEnd->copy()->endOfDay()
                    ])->sum('amount') ?? 0
                ];
                $current->addMonth()->startOfMonth();
            }
            return $data;
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
