<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\Attributes\Validate;

class CustomerForm extends Component
{
    public ?Customer $customer = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:50')]
    public $phone = '';

    #[Validate('required|string')]
    public $address = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->customer = Customer::findOrFail($id);
            $this->name = $this->customer->name;
            $this->phone = $this->customer->phone;
            $this->address = $this->customer->address;
        }
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->customer) {
            $this->customer->update($data);
            session()->flash('success', 'Customer updated successfully!');
        } else {
            Customer::create($data);
            session()->flash('success', 'Customer created successfully!');
        }

        return redirect()->route('customers.index');
    }

    public function cancel()
    {
        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customer-form')->layout('layouts.app');
    }
}
