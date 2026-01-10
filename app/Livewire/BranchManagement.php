<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BranchManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $branch = Branch::findOrFail($id);
            Employee::where('branch_id', $branch->id)->update(['branch_id' => null]);
            $branch->delete();
        });

        session()->flash('success', 'Branch deleted successfully!');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Branch::query()->with(['employees.user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $branches = $query->orderBy('created_at', 'desc')
                          ->paginate($this->perPage);

        return view('livewire.branch-management', [
            'branches' => $branches,
        ])->layout('layouts.app');
    }
}
