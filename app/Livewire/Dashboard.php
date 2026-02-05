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
    public $branchId = null;
    public $branches = [];
    public $canSelectBranch = false;

    // Metrics
    public $grossSales = 0;
    public $netSales = 0;
    public $finalNetSales = 0;
    public $todaySales = 0;
    public $todayExpenses = 0;
    public $todayNetSales = 0;
    public $todayPaymentBreakdown = [];
    public $totalProductSales = 0;
    public $totalServiceSales = 0;
    public $totalVipSales = 0;
    public $totalUpholsterySales = 0;
    public $expenseInternal = 0;
    public $expenseCharged = 0;
    public $totalBusinessExpenses = 0;

    // Today's data for non-admin
    public $todaysOrders = [];
    public $todaysExpenses = [];
    public $todaysPayments = [];

    // Customer & inventory
    public $topCustomers = [];
    public $lowStockProducts = [];
    public $inventoryValue = 0;
    public $topProducts = [];

    // Charts
    public $revenueChartData = [];
    public $ordersChartData = [];

    public function mount()
    {
        $this->initBranchAccess();
        $this->branches = Branch::active()->orderBy('name')->get();
        $this->loadOrCreateFilter();
        $this->loadDashboardData();
    }

    private function initBranchAccess(): void
    {
        $user = auth()->user();
        $this->canSelectBranch = $user && $user->role === 'admin';
    }

    public function loadDashboardData(): void
    {
        $this->loadRevenueMetrics();
        $this->loadCustomerMetrics();
        $this->loadInventoryMetrics();
        $this->loadProductMetrics();
        $this->loadChartData();
        if (!$this->canSelectBranch) {
            $this->loadTodaysOrders();
            $this->loadTodaysExpenses();
            $this->loadTodaysPayments();
        }
    }

    private function loadOrCreateFilter(): void
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

    private function setDefaultDates(): void
    {
        $this->start_date = Carbon::today()->subDays(29)->format('Y-m-d');
        $this->end_date = Carbon::today()->format('Y-m-d');
    }

    private function getDateRange(): array
    {
        return [
            Carbon::parse($this->start_date)->startOfDay(),
            Carbon::parse($this->end_date)->endOfDay()
        ];
    }

    private function loadRevenueMetrics(): void
    {
        [$start, $end] = $this->getDateRange();
        $ordersRange = $this->ordersRangeQuery($start, $end);
        $this->grossSales = (int) ($ordersRange->sum('total_gross') ?? 0);
        $this->netSales = (int) ($ordersRange->sum('net_income') ?? 0);

        // Today's sales
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $todayPaymentsQuery = \App\Models\Payment::whereBetween('paid_at', [$todayStart, $todayEnd]);
        if ($this->branchId) {
            $todayPaymentsQuery->whereHas('order', function($q) {
                $q->where('branch_id', $this->branchId);
            });
        }
        $this->todaySales = (int) ($todayPaymentsQuery->sum('amount') ?? 0);
        $this->todayPaymentBreakdown = $todayPaymentsQuery->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        // Today's business expenses
        $todayExpensesQuery = Expense::whereBetween('created_at', [$todayStart, $todayEnd]);
        if ($this->branchId) {
            $todayExpensesQuery->where('branch_id', $this->branchId);
        }
        $this->todayExpenses = (int) ($todayExpensesQuery->sum('amount') ?? 0);
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

        $standaloneExpensesRange = Expense::whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $standaloneExpensesRange->where('branch_id', $this->branchId);
        $this->totalBusinessExpenses = (int) ($standaloneExpensesRange->sum('amount') ?? 0);
        $this->finalNetSales = $this->grossSales - $this->totalBusinessExpenses;
    }

    private function ordersRangeQuery($start, $end)
    {
        $query = Order::query()->whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $query->where('branch_id', $this->branchId);
        return $query;
    }

    private function loadCustomerMetrics(): void
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

    private function loadInventoryMetrics(): void
    {
        $lowStockQ = Product::query()->lowStock()->orderBy('stock_qty', 'asc');
        if ($this->branchId) $lowStockQ->where('branch_id', $this->branchId);
        $this->lowStockProducts = $lowStockQ->get()->toArray();

        $inventoryQ = Product::selectRaw('SUM(stock_qty * buy_price) as total_value');
        if ($this->branchId) $inventoryQ->where('branch_id', $this->branchId);
        $this->inventoryValue = $inventoryQ->value('total_value') ?? 0;
    }

    private function loadProductMetrics(): void
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

    private function loadChartData(): void
    {
        $this->revenueChartData = $this->getRevenueChartData();
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

    public function switchBranch($branchId): void
    {
        if (! $this->canSelectBranch) return;
        $this->branchId = $branchId === 'all' ? null : $branchId;
        $this->applyFilters();
    }

    public function applyFilters(): void
    {
        if (! $this->canSelectBranch) {
            $this->branchId = auth()->user()?->branch_id;
        }
        $this->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $this->storeFilter();
        $this->redirect(route('dashboard.index'), navigate: false);
    }

    private function storeFilter(): void
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

    private function getRevenueChartData(): array
    {
        [$start, $end] = $this->getDateRange();
        $diffInDays = $start->diffInDays($end);
        $base = Order::query()->whereBetween('created_at', [$start, $end]);
        if ($this->branchId) $base->where('branch_id', $this->branchId);
        if ($diffInDays <= 1) {
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
            $data = [];
            $current = $start->copy();
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->addDays(6);
                if ($weekEnd->gt($end)) $weekEnd = $end->copy();
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
        $data = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $monthEnd = $current->copy()->endOfMonth();
            if ($monthEnd->gt($end)) $monthEnd = $end->copy();
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

    private function loadTodaysPayments(): void
    {
        // This method always loads payments for "today" only, not affected by dashboard date filters.
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $paymentsQuery = \App\Models\Payment::whereBetween('paid_at', [$todayStart, $todayEnd])
            ->with(['order.customer', 'order.branch', 'order.orderItems']);
        if ($this->branchId) {
            $paymentsQuery->whereHas('order', function($q) {
                $q->where('branch_id', $this->branchId);
            });
        }
        $this->todaysPayments = $paymentsQuery->get()->map(function($payment) {
            $order = $payment->order;
            return [
                'order_id' => $order?->id,
                'customer_name' => $order?->customer_name ?? $order?->customer?->name ?? 'N/A',
                'amount' => $order?->total_amount ?? 0,
                'payment_status' => $order?->payment_status ?? 'N/A',
                'balance' => ($order?->total_amount ?? 0) - ($order?->payments()->sum('amount') ?? 0),
                'paid_amount' => $payment->amount,
                'order_status' => $order?->status ?? 'N/A',
                'order_branch' => $order?->branch?->name ?? 'N/A',
                'order_items' => $order?->orderItems?->map(function($item) {
                    return [
                        'item_name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,
                    ];
                })->toArray() ?? [],
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i') : '',
                'order' => $order,
            ];
        })->toArray();
    }

    private function loadTodaysOrders(): void
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $ordersQuery = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->with(['customer', 'orderItems.product', 'orderItems.service'])
            ->orderBy('created_at', 'desc');
        if ($this->branchId) $ordersQuery->where('branch_id', $this->branchId);
        $this->todaysOrders = $ordersQuery->get()->map(fn($order) => [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at->format('h:i A'),
            'items' => $order->orderItems->map(fn($item) => [
                'name' => $item->product ? $item->product->name : ($item->service ? $item->service->name : 'Unknown'),
                'quantity' => $item->quantity,
                'total_price' => $item->total_price,
            ])->toArray(),
        ])->toArray();
    }

    private function loadTodaysExpenses(): void
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $expensesQuery = Expense::whereBetween('created_at', [$todayStart, $todayEnd])
            ->orderBy('created_at', 'desc');
        if ($this->branchId) $expensesQuery->where('branch_id', $this->branchId);
        $this->todaysExpenses = $expensesQuery->get()->map(fn($expense) => [
            'id' => $expense->id,
            'category' => $expense->category,
            'description' => $expense->description,
            'amount' => $expense->amount,
            'created_at' => $expense->created_at->format('h:i A'),
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'todayPaymentBreakdown' => $this->todayPaymentBreakdown,
            'todaysPayments' => $this->todaysPayments,
        ])->layout('layouts.app');
    }
}
