<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Collection;

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

        // Build the base query with order relationship filters
        $query = OrderItem::whereHas('order', function ($q) use ($dateStart, $dateEnd) {
            $q->whereBetween('created_at', [$dateStart, $dateEnd])
              ->where('payment_status', '!=', 'cancelled');
            
            // Apply branch filtering
            if (!$this->isAdmin) {
                $q->where('branch_id', auth()->user()?->branch_id);
            }
        });

        // Filter by item type based on category and sum total_price from order items
        return match($category) {
            'accessories' => $query->whereNotNull('product_id')->sum('total_price') ?? 0,
            'services' => $query->whereNotNull('service_id')->sum('total_price') ?? 0,
            'upholstery' => $query->whereNotNull('upholstery_id')->sum('total_price') ?? 0,
            'vip' => $query->whereNotNull('vip_id')->sum('total_price') ?? 0,
            default => 0,
        };
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
