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
    public array $overallPayments = [];
    public array $branchPayments = [];
    public $branches;
    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->initializeProperties();
        $this->loadReport();
    }

    public function updatedReportDate(): void
    {
        $this->validate([
            'reportDate' => 'required|date',
        ]);
        $this->loadReport();
    }

    private function initializeProperties(): void
    {
        $this->reportDate = Carbon::today()->format('Y-m-d');
        $this->isAdmin = auth()->user()?->isAdmin();
        $this->branches = Branch::active()->orderBy('name')->get();
    }

    private function loadReport(): void
    {
        $date = Carbon::parse($this->reportDate)->format('Y-m-d');

        $this->overallSummary = $this->getSummary($date);
        $this->overallPayments = $this->getPayments($date);

        $branchCollection = $this->getBranchCollection();

        $this->branchPayments = $this->getBranchPayments($branchCollection, $date);
        $this->branchSummaries = $this->getBranchSummaries($branchCollection, $date);
        
        // Add sold items by type to overall summary
        $this->overallSummary['soldItemsByType'] = $this->getSoldItemsByType($date);
    }

    private function getBranchCollection()
    {
        return $this->isAdmin
            ? $this->branches
            : $this->branches->where('id', auth()->user()?->branch_id);
    }

    private function getBranchPayments($branchCollection, string $date): array
    {
        return $branchCollection
            ->mapWithKeys(fn($branch) => [
                $branch->id => $this->getPayments($date, $branch->id)
            ])->toArray();
    }

    private function getBranchSummaries($branchCollection, string $date): array
    {
        return $branchCollection
            ->map(fn($branch) => [
                'branch' => $branch,
                ...$this->getSummary($date, $branch->id),
                'payments' => $this->branchPayments[$branch->id] ?? [],
            ])
            ->values()
            ->toArray();
    }

    private function getPayments(string $date, ?int $branchId = null): array
    {
        $paymentsQuery = \App\Models\Payment::whereDate('paid_at', $date)
            ->with(['order.customer']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $payments = $paymentsQuery->get();
        return $payments->map(fn($payment) => $this->formatPayment($payment))->toArray();
    }

    private function formatPayment($payment): array
    {
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
            'order_items' => $order?->orderItems?->map(fn($item) => [
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price,
            ])->toArray() ?? [],
            'order' => $order, // for fallback in blade if needed
        ];
    }

    private function getSummary(string $date, ?int $branchId = null): array
    {
        $orders = $this->getOrders($date, $branchId);
        $orderItems = $orders->flatMap->orderItems->values();
        $totalSales = (int) $orders->sum('total_amount');
        $orderCount = $orders->count();

        $productLogs = $this->getProductLogs($date, $branchId);
        $supplyIn = (int) $productLogs->where('change_amount', '>', 0)->sum('change_amount');
        $supplyOut = (int) abs($productLogs->where('change_amount', '<', 0)->sum('change_amount'));

        $expenses = $this->getExpenses($date, $branchId);
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

    private function getOrders(string $date, ?int $branchId = null)
    {
        $query = Order::query()
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
            $query->where('branch_id', $branchId);
        }
        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getProductLogs(string $date, ?int $branchId = null)
    {
        $query = ProductLog::query()
            ->whereDate('created_at', $date)
            ->with('product');
        $query->whereHas('product', function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            } elseif (! auth()->user()?->isAdmin()) {
                $q->where('branch_id', auth()->user()->branch_id);
            }
        });
        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getExpenses(string $date, ?int $branchId = null)
    {
        $query = Expense::query()->whereDate('expense_date', $date);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        return $query->orderBy('expense_date', 'desc')->get();
    }

    private function getSoldItemsByType(string $date, ?int $branchId = null): array
    {
        $orders = $this->getOrders($date, $branchId);
        $orderItems = $orders->flatMap->orderItems->values();

        $retailItems = $orderItems
            ->filter(fn($item) => $item->product && $item->product->type === 'retail')
            ->groupBy('item_name')
            ->map(fn($items) => [
                'item_name' => $items->first()->item_name,
                'product_id' => $items->first()->product_id,
                'quantity' => (int) $items->sum('quantity'),
                'total_price' => (int) $items->sum('total_price'),
                'unit_price' => (int) ($items->first()->unit_price ?? 0),
            ])
            ->values();

        $materialItems = $orderItems
            ->filter(fn($item) => $item->product && $item->product->type === 'material')
            ->groupBy('item_name')
            ->map(fn($items) => [
                'item_name' => $items->first()->item_name,
                'product_id' => $items->first()->product_id,
                'quantity' => (int) $items->sum('quantity'),
                'total_price' => (int) $items->sum('total_price'),
                'unit_price' => (int) ($items->first()->unit_price ?? 0),
            ])
            ->values();

        return [
            'retail' => $retailItems->toArray(),
            'material' => $materialItems->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.daily-report')->layout('layouts.app');
    }
}
