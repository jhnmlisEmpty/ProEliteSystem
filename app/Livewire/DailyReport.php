<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use App\Models\ProductLog;
use Carbon\Carbon;
use Livewire\Component;

class DailyReport extends Component
{
    public string $reportDate;
    public array $overallSummary = [];
    public array $branchSummaries = [];
    public $branches;
    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->reportDate = Carbon::today()->format('Y-m-d');
        $this->isAdmin = auth()->user()?->isAdmin();
        $this->branches = Branch::active()->orderBy('name')->get();
        $this->loadReport();
    }

    public function updatedReportDate(): void
    {
        $this->validate([
            'reportDate' => 'required|date',
        ]);

        $this->loadReport();
    }

    private function loadReport(): void
    {
        $date = Carbon::parse($this->reportDate)->format('Y-m-d');

        $this->overallSummary = $this->buildSummary($date, null);

        $branchCollection = $this->isAdmin
            ? $this->branches
            : $this->branches->where('id', auth()->user()?->branch_id);

        $this->branchSummaries = $branchCollection
            ->map(fn($branch) => [
                'branch' => $branch,
                ...$this->buildSummary($date, $branch->id),
            ])
            ->values()
            ->toArray();
    }

    private function buildSummary(string $date, ?int $branchId): array
    {
        $ordersQuery = Order::query()
            ->with([
                'orderItems.product',
                'orderItems.service',
                'orderItems.upholstery',
                'orderItems.vip',
                'customer',
                'branch',
            ])
            ->whereDate('created_at', $date);

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();
        $orderItems = $orders->flatMap->orderItems->values();
        $totalSales = (int) $orders->sum('total_amount');
        $orderCount = $orders->count();

        $productLogsQuery = ProductLog::query()
            ->whereDate('created_at', $date)
            ->with('product');

        $productLogsQuery->whereHas('product', function ($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } elseif (! auth()->user()?->isAdmin()) {
                $query->where('branch_id', auth()->user()->branch_id);
            }
        });

        $productLogs = $productLogsQuery->orderBy('created_at', 'desc')->get();
        $supplyIn = (int) $productLogs->where('change_amount', '>', 0)->sum('change_amount');
        $supplyOut = (int) abs($productLogs->where('change_amount', '<', 0)->sum('change_amount'));

        $expensesQuery = Expense::query()->whereDate('expense_date', $date);

        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }

        $expenses = $expensesQuery->orderBy('expense_date', 'desc')->get();
        $expenseTotal = (int) $expenses->sum('amount');

        return [
            'orders' => $orders,
            'orderItems' => $orderItems,
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'productLogs' => $productLogs,
            'supplyIn' => $supplyIn,
            'supplyOut' => $supplyOut,
            'expenses' => $expenses,
            'expenseTotal' => $expenseTotal,
        ];
    }

    public function render()
    {
        return view('livewire.daily-report')->layout('layouts.app');
    }
}
