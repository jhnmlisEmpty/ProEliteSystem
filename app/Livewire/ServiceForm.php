<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ServiceForm extends Component
{
    public ?Service $service = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|integer|min:0')]
    public $base_labor_cost = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->service = Service::findOrFail($id);
            $this->name = $this->service->name;
            $this->base_labor_cost = $this->service->base_labor_cost;
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
        return view('livewire.service-form')->layout('layouts.app');
    }
}
