<?php

namespace App\Livewire;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $confirmingDeletion = false;
    public $expenseToDelete = null;

    protected $queryString = ['search', 'categoryFilter', 'startDate', 'endDate'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->expenseToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function deleteExpense()
    {
        if ($this->expenseToDelete) {
            $expense = Expense::find($this->expenseToDelete);
            if ($expense) {
                $expense->delete();
                session()->flash('success', 'Expense deleted successfully.');
            }
        }

        $this->confirmingDeletion = false;
        $this->expenseToDelete = null;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->expenseToDelete = null;
    }

    public function render()
    {
        $expenses = Expense::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                        ->orWhere('category', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->startDate, function ($query) {
                $query->whereDate('expense_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('expense_date', '<=', $this->endDate);
            })
            ->orderBy('expense_date', 'desc')
            ->paginate(15);

        $totalAmount = Expense::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                        ->orWhere('category', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->startDate, function ($query) {
                $query->whereDate('expense_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('expense_date', '<=', $this->endDate);
            })
            ->sum('amount');

        $categories = Expense::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->map(fn($cat) => ucfirst(str_replace('_', ' ', $cat)))
            ->sort();

        return view('livewire.expense-management', [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}
