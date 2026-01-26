<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Filter;
use App\Models\OrderExpense;
use App\Models\Expense;
use App\Models\Branch;
use Carbon\Carbon;


class Dashboard extends Component
{
    // Filters
    public $start_date;
    public $end_date;
    public $branchId = null; // null = all branches
    public $branches = [];
    public $canSelectBranch = false;

    // Sales (range)
    public $grossSales = 0; // sum of orders.total_gross
    public $netSales = 0;   // sum of orders.net_income
    public $finalNetSales = 0; // netSales - totalBusinessExpenses
    public $todaySales = 0; // sum of today's payments
    public $todayExpenses = 0; // sum of today's business expenses
    public $todayNetSales = 0; // todaySales - todayExpenses
    public $completedOrdersSales = 0; // sum of completed orders total_amount
    public $completedOrdersCount = 0; // count of completed orders
    public $totalProductSales = 0; // sum of order_items total where product
    public $totalServiceSales = 0; // sum of order_items total where service
    public $totalVipSales = 0; // sum of order_items total where vip package
    public $totalUpholsterySales = 0; // sum of order_items total where upholstery
    public $expenseInternal = 0;   // sum of order_expenses.my_cost
    public $expenseCharged = 0;    // sum of order_expenses.charge_client
    public $totalBusinessExpenses = 0; // sum of standalone Expense table amounts

    // Today's orders for non-admin
    public $todaysOrders = [];
    public $todaysExpenses = [];

    // Customer metrics
    public $topCustomers = [];

    // Inventory metrics
    public $lowStockProducts = [];
    public $inventoryValue = 0;
    public $topProducts = [];

    // Charts
    public $revenueChartData = [];
    public $ordersChartData = [];

    public function mount()
    {
        $user = auth()->user();
        // Only admins can view cross-branch data; others are locked to their branch
        $this->canSelectBranch = $user && $user->role === 'admin';

        $this->branches = Branch::active()->orderBy('name')->get();
        $this->loadOrCreateFilter();
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->loadRevenueMetrics();
        $this->loadCustomerMetrics();
        $this->loadInventoryMetrics();
        $this->loadProductMetrics();
        $this->loadChartData();
        
        // Load today's orders for non-admin users
        if (!$this->canSelectBranch) {
            $this->loadTodaysOrders();
            $this->loadTodaysExpenses();
        }
    }

    private function loadOrCreateFilter()
    {
        $user = auth()->user();
        $filter = Filter::where('filter_type', 'dashboard')
            ->where('user_id', auth()->id())
            ->first();
        
        if ($filter) {
            $this->start_date = $filter->start_date->format('Y-m-d');
            $this->end_date = $filter->end_date->format('Y-m-d');
            $this->branchId = $filter->branch_id;
        } else {
            $this->setDefaultDates();
            $this->storeFilter();
        }

        if (! $this->canSelectBranch) {
            $this->branchId = $user?->branch_id;
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
        // Range-based sales/expense metrics
        [$start, $end] = $this->getDateRange();

        $ordersRange = Order::query()->whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $ordersRange->where('branch_id', $this->branchId);
        $this->grossSales = (int) ($ordersRange->sum('total_gross') ?? 0);
        $this->netSales = (int) ($ordersRange->sum('net_income') ?? 0);

        // Today's sales based on payments received today
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $todayPaymentsQuery = \App\Models\Payment::whereBetween('paid_at', [$todayStart, $todayEnd]);
        
        if ($this->branchId) {
            $todayPaymentsQuery->whereHas('order', function($q) {
                $q->where('branch_id', $this->branchId);
            });
        }
        
        $this->todaySales = (int) ($todayPaymentsQuery->sum('amount') ?? 0);

        // Today's business expenses
        $todayExpensesQuery = Expense::whereBetween('created_at', [$todayStart, $todayEnd]);
        if ($this->branchId) {
            $todayExpensesQuery->where('branch_id', $this->branchId);
        }
        $this->todayExpenses = (int) ($todayExpensesQuery->sum('amount') ?? 0);

        // Today's net sales
        $this->todayNetSales = $this->todaySales - $this->todayExpenses;

       

        $orderItemsRange = OrderItem::whereHas('order', function($q) use ($start, $end){
            $q->whereBetween('created_at', [$start, $end]);
            if ($this->branchId) $q->where('branch_id', $this->branchId);
        });
        $this->totalProductSales = (int) ($orderItemsRange->clone()->whereNotNull('product_id')->sum('total_price') ?? 0);
        $this->totalServiceSales = (int) ($orderItemsRange->clone()->whereNotNull('service_id')->sum('total_price') ?? 0);
        $this->totalVipSales = (int) ($orderItemsRange->clone()->whereNotNull('vip_id')->sum('total_price') ?? 0);
        $this->totalUpholsterySales = (int) ($orderItemsRange->clone()->whereNotNull('upholstery_id')->sum('total_price') ?? 0);

        $expensesRange = OrderExpense::whereHas('order', function($q) use ($start, $end){
            $q->whereBetween('created_at', [$start, $end]);
            if ($this->branchId) $q->where('branch_id', $this->branchId);
        });
        $this->expenseInternal = (int) ($expensesRange->sum('my_cost') ?? 0);
        $this->expenseCharged = (int) ($expensesRange->sum('charge_client') ?? 0);

        // Load standalone business expenses
        $standaloneExpensesRange = Expense::whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $standaloneExpensesRange->where('branch_id', $this->branchId);
        $this->totalBusinessExpenses = (int) ($standaloneExpensesRange->sum('amount') ?? 0);

        // Calculate final net sales = net income - business expenses
        $this->finalNetSales = $this->netSales - $this->totalBusinessExpenses;
    }

    private function loadCustomerMetrics()
    {
        [$start, $end] = $this->getDateRange();
        $ordersTop = Order::selectRaw('customer_id, SUM(total_amount) as total_spent')
            ->whereBetween('created_at', [$start, $end])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->with('customer')
            ->take(5)
            ->get();
        $this->topCustomers = $ordersTop
            ->map(fn($order) => [
                'name' => $order->customer->name ?? 'N/A',
                'total_spent' => $order->total_spent,
            ])
            ->toArray();
    }

    private function loadInventoryMetrics()
    {
        $lowStockQ = Product::query()->lowStock()->orderBy('stock_qty', 'asc');
        if ($this->branchId) $lowStockQ->where('branch_id', $this->branchId);
        $this->lowStockProducts = $lowStockQ->get()
            ->toArray();
        
        $inventoryQ = Product::selectRaw('SUM(stock_qty * buy_price) as total_value');
        if ($this->branchId) $inventoryQ->where('branch_id', $this->branchId);
        $this->inventoryValue = $inventoryQ->value('total_value') ?? 0;
    }

    private function loadProductMetrics()
    {
        [$start, $end] = $this->getDateRange();

        $this->topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_price) as total_sales')
            ->whereNotNull('product_id')
            ->whereHas('order', function($q) use ($start, $end){
                $q->whereBetween('created_at', [$start, $end]);
                if ($this->branchId) $q->where('branch_id', $this->branchId);
            })
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
    }

    private function loadChartData()
    {
        $this->revenueChartData = $this->getRevenueChartData();
        // Order status distribution within range + branch
        [$start, $end] = $this->getDateRange();
        $base = Order::query()->whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $base->where('branch_id', $this->branchId);
        $this->ordersChartData = [
            ['status' => 'Pending', 'count' => (clone $base)->where('status','pending')->count()],
            ['status' => 'In Progress', 'count' => (clone $base)->where('status','in_progress')->count()],
            ['status' => 'Completed', 'count' => (clone $base)->where('status','completed')->count()],
            ['status' => 'Cancelled', 'count' => (clone $base)->where('status','cancelled')->count()],
        ];
    }

    public function switchBranch($branchId)
    {
        if (! $this->canSelectBranch) {
            return;
        }

        $this->branchId = $branchId === 'all' ? null : $branchId;
        $this->applyFilters();
    }

    public function applyFilters()
    {
        if (! $this->canSelectBranch) {
            $this->branchId = auth()->user()?->branch_id;
        }

        $this->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        // Store the filter dates in database
        $this->storeFilter();
        
        // Force full page reload
        $this->redirect(route('dashboard.index'), navigate: false);
    }

    /**
     * Store or update the filter dates in the database
     */
    private function storeFilter()
    {
        $branchId = $this->canSelectBranch
            ? ($this->branchId ?: null)
            : auth()->user()?->branch_id;

        Filter::updateOrCreate(
            [
                'filter_type' => 'dashboard',
                'user_id' => auth()->id(),
            ],
            [
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'branch_id' => $branchId,
            ]
        );
    }

    private function getRevenueChartData()
    {
        [$start, $end] = $this->getDateRange();
        $diffInDays = $start->diffInDays($end);

        $base = Order::query()->whereBetween('created_at', [$start, $end]);
        if ($this->branchId) {
            $base->where('branch_id', $this->branchId);
        }

        if ($diffInDays <= 1) {
            // Hourly grouping for 1 day or less
            $data = [];
            $current = $start->copy()->startOfHour();
            $lastHour = $end->copy()->endOfHour();

            while ($current->lte($lastHour)) {
                $hourEnd = $current->copy()->endOfHour();
                $data[] = [
                    'date' => $current->format('M d, H:i'),
                    'amount' => (clone $base)->whereBetween('created_at', [$current, $hourEnd])->sum('total_amount') ?? 0,
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
                    'amount' => (clone $base)->whereDate('created_at', $current)->sum('total_amount') ?? 0,
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
                    'amount' => (clone $base)->whereBetween('created_at', [
                        $current->copy()->startOfDay(),
                        $weekEnd->copy()->endOfDay()
                    ])->sum('total_amount') ?? 0,
                ];
                $current->addWeek();
            }

            return $data;
        }

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
                'amount' => (clone $base)->whereBetween('created_at', [
                    $current->copy()->startOfDay(),
                    $monthEnd->copy()->endOfDay()
                ])->sum('total_amount') ?? 0,
            ];
            $current->addMonth()->startOfMonth();
        }

        return $data;
    }

    private function loadTodaysOrders()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        
        $ordersQuery = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->with(['customer', 'orderItems.product', 'orderItems.service'])
            ->orderBy('created_at', 'desc');
        
        if ($this->branchId) {
            $ordersQuery->where('branch_id', $this->branchId);
        }
        
        $this->todaysOrders = $ordersQuery->get()->map(function($order) {
            return [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('h:i A'),
                'items' => $order->orderItems->map(function($item) {
                    return [
                        'name' => $item->product ? $item->product->name : ($item->service ? $item->service->name : 'Unknown'),
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function loadTodaysExpenses()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        
        $expensesQuery = Expense::whereBetween('created_at', [$todayStart, $todayEnd])
            ->orderBy('created_at', 'desc');
        
        if ($this->branchId) {
            $expensesQuery->where('branch_id', $this->branchId);
        }
        
        $this->todaysExpenses = $expensesQuery->get()->map(function($expense) {
            return [
                'id' => $expense->id,
                'category' => $expense->category,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'created_at' => $expense->created_at->format('h:i A'),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
