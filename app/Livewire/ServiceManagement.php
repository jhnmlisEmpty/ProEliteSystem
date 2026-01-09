<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $branchFilter = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        session()->flash('success', 'Service deleted successfully!');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->branchFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Service::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        $services = $query->orderBy('created_at', 'desc')
                         ->paginate($this->perPage);

        $branches = \App\Models\Branch::where('is_active', true)->get();
        $canFilterBranch = in_array(auth()->user()->role, ['admin', 'manager']);

        return view('livewire.service-management', [
            'services' => $services,
            'branches' => $branches,
            'canFilterBranch' => $canFilterBranch
        ])->layout('layouts.app');
    }
}
