<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Livewire\Component;

class SalesSummaryReport extends Component
{
    public string $startDate;
    public string $endDate;
    public array $reportData = [];
    public array $summary = [];
    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->isAdmin = auth()->user()?->isAdmin();
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadReport();
    }

    public function updatedStartDate(): void
    {
        $this->validateDates();
        $this->loadReport();
    }

    public function updatedEndDate(): void
    {
        $this->validateDates();
        $this->loadReport();
    }

    private function validateDates(): void
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
    }

    private function loadReport(): void
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Get all dates in range
        $dates = $this->generateDateRange($start, $end);

        // Categories
        $categories = ['accessories', 'upholstery', 'vip', 'services'];

        // Build report data for each date
        $this->reportData = [];
        foreach ($dates as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayLabel = $date->format('d-M');

            $row = [
                'date' => $dateStr,
                'label' => $dayLabel,
            ];

            foreach ($categories as $category) {
                $sales = $this->getSalesByCategory($category, $dateStr);
                $expenses = $this->getExpensesByCategory($category, $dateStr);
                
                $row[$category] = [
                    'sales' => $sales,
                    'expenses' => $expenses,
                    'net' => $sales - $expenses,
                ];
            }

            $this->reportData[] = $row;
        }

        // Calculate summary
        $this->calculateSummary($categories);
    }

    private function generateDateRange(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        return $dates;
    }

    private function getSalesByCategory(string $category, string $date): int
    {
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // Determine the column to filter by category
        $column = match($category) {
            'accessories' => 'product_id',
            'services' => 'service_id',
            'upholstery' => 'upholstery_id',
            'vip' => 'vip_id',
            default => null,
        };

        if (!$column) return 0;

        // Other columns to ensure only this category is counted
        $otherColumns = array_values(array_diff(
            ['product_id', 'service_id', 'upholstery_id', 'vip_id'],
            [$column]
        ));

        // Get orders that received a payment on this date, then sum item totals directly
        $query = Order::whereHas('payments', function ($paymentQuery) use ($dateStart, $dateEnd) {
            $paymentQuery->whereBetween('paid_at', [$dateStart, $dateEnd]);
        })->with(['orderItems']);
        
        // Apply branch filtering
        if (!$this->isAdmin) {
            $query->where('branch_id', auth()->user()?->branch_id);
        }

        $orders = $query->get();

        // Sum the actual item totals for the category.
        return $orders->reduce(function ($carry, $order) use ($column, $otherColumns) {
            
            // Get items matching this category (this column not null, others null)
            $categoryItems = $order->orderItems->filter(function($item) use ($column, $otherColumns) {
                if ($item->{$column} === null) return false;
                foreach ($otherColumns as $col) {
                    if ($item->{$col} !== null) return false;
                }
                return true;
            });
            
            $categoryTotal = $categoryItems->sum('total_price');

            // Discount applies only to accessories (products)
            if ($column === 'product_id') {
                $categoryTotal = max(0, $categoryTotal - ($order->discounted_amount ?? 0));
            }

            return $carry + $categoryTotal;
        }, 0);
    }

    private function getExpensesByCategory(string $category, string $date): int
    {
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        $query = Expense::whereBetween('expense_date', [$dateStart, $dateEnd])
            ->where('category', $category);

        // Apply branch filtering
        if (!$this->isAdmin) {
            $query->where('branch_id', auth()->user()?->branch_id);
        }

        return $query->sum('amount') ?? 0;
    }

    private function calculateSummary(array $categories): void
    {
        $this->summary = [];

        foreach ($categories as $category) {
            $totalSales = 0;
            $totalExpenses = 0;

            foreach ($this->reportData as $row) {
                $totalSales += $row[$category]['sales'] ?? 0;
                $totalExpenses += $row[$category]['expenses'] ?? 0;
            }

            $this->summary[$category] = [
                'sales' => $totalSales,
                'expenses' => $totalExpenses,
                'net' => $totalSales - $totalExpenses,
            ];
        }
    }

    public function render()
    {
        return view('livewire.sales-summary-report')->layout('layouts.app');
    }
}
