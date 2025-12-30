<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $paymentFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->paymentFilter = '';
        $this->resetPage();
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        
        // Only allow deleting pending orders
        if ($order->status !== 'pending') {
            session()->flash('error', 'Only pending orders can be deleted.');
            return;
        }

        $order->delete();
        session()->flash('success', 'Order deleted successfully.');
    }

    public function render()
    {
        $query = Order::with(['customer', 'orderItems'])
            ->when($this->search, function ($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('plate_number', 'like', '%' . $this->search . '%')
                    ->orWhere('id', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->paymentFilter, function ($q) {
                $q->where('payment_status', $this->paymentFilter);
            })
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(15);

        return view('livewire.order-management', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}
