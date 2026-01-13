<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Branch;
use Livewire\Component;

class ExpenseForm extends Component
{
    public $expenseId = null;
    public $branch_id;
    public $category = '';
    public $description = '';
    public $amount = '';
    public $expense_date;
    public $notes = '';

    public $branches = [];
    public $canSelectBranch = false;

    protected $rules = [
        'branch_id' => 'required|exists:branches,id',
        'category' => 'nullable|string|max:255',
        'description' => 'required|string|min:3|max:255',
        'amount' => 'required|numeric|min:0',
        'expense_date' => 'required|date',
        'notes' => 'nullable|string|max:1000',
    ];

    public const CATEGORIES = [
        'utilities' => 'Utilities (Water, Electricity, Gas)',
        'supplies' => 'Office & Shop Supplies',
        'maintenance' => 'Maintenance & Repairs',
        'rent' => 'Rent & Lease',
        'insurance' => 'Insurance',
        'advertising' => 'Advertising & Marketing',
        'fuel' => 'Fuel & Transportation',
        'salaries' => 'Salaries & Wages',
        'equipment' => 'Equipment & Tools',
        'subscriptions' => 'Software & Subscriptions',
        'other' => 'Other Expenses',
    ];

    public function mount($id = null)
    {
        $user = auth()->user();
        $this->canSelectBranch = $user->role === 'admin';
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Auto-select branch for non-admin users
        if (!$this->canSelectBranch) {
            $this->branch_id = $user->branch_id;
        }

        // Set default date to today
        $this->expense_date = now()->format('Y-m-d');

        // Load expense for editing
        if ($id) {
            $expense = Expense::findOrFail($id);
            $this->expenseId = $expense->id;
            $this->branch_id = $expense->branch_id;
            $this->category = $expense->category;
            $this->description = $expense->description;
            $this->amount = $expense->amount;
            $this->expense_date = $expense->expense_date->format('Y-m-d');
            $this->notes = $expense->notes;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        Expense::updateOrCreate(
            ['id' => $this->expenseId],
            $validated
        );

        session()->flash('success', $this->expenseId ? 'Expense updated successfully!' : 'Expense created successfully!');
        
        return redirect()->route('expenses.index');
    }

    public function cancel()
    {
        return redirect()->route('expenses.index');
    }

    public function render()
    {
        return view('livewire.expense-form', [
            'categories' => self::CATEGORIES,
        ])->layout('layouts.app');
    }
}
