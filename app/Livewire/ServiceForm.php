<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ServiceForm extends Component
{
    public ?Service $service = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|integer|min:0')]
    public $base_labor_cost = null;
    
    #[Validate('required|exists:branches,id')]
    public $branch_id;

    public function mount(?int $id = null): void
    {
        // Set default branch based on user role
        if (!$id) {
            $user = auth()->user();
            if ($user->role === 'user') {
                $this->branch_id = $user->branch_id;
            }
        }
        
        if ($id) {
            $this->service = Service::findOrFail($id);
            $this->name = $this->service->name;
            $this->base_labor_cost = $this->service->base_labor_cost;
            $this->branch_id = $this->service->branch_id;
        }
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->service) {
            $this->service->update($data);
            session()->flash('success', 'Service updated successfully!');
        } else {
            Service::create($data);
            session()->flash('success', 'Service created successfully!');
        }

        return redirect()->route('services.index');
    }

    public function cancel()
    {
        return redirect()->route('services.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $user = auth()->user();
        $canSelectBranch = in_array($user->role, ['admin', 'manager']);
        
        return view('livewire.service-form', [
            'branches' => $branches,
            'canSelectBranch' => $canSelectBranch,
        ])->layout('layouts.app');
    }
}
