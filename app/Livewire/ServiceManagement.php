<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceManagement extends Component
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
        $service = Service::findOrFail($id);
        $service->delete();

        session()->flash('success', 'Service deleted successfully!');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Service::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $services = $query->orderBy('created_at', 'desc')
                         ->paginate($this->perPage);

        return view('livewire.service-management', [
            'services' => $services,
        ])->layout('layouts.app');
    }
}
