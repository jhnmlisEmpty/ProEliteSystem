<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManagement extends Component
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
        $customer = Customer::findOrFail($id);
        $customer->delete();

        session()->flash('success', 'Customer deleted successfully!');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Customer::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $customers = $query->orderBy('created_at', 'desc')
                         ->paginate($this->perPage);

        return view('livewire.customer-management', [
            'customers' => $customers
        ])->layout('layouts.app');
    }
}
