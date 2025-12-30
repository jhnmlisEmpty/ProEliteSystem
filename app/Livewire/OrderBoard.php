<?php

namespace App\Livewire;

use App\Models\JobOrder;
use Livewire\Component;

class OrderBoard extends Component
{
    public $pendingOrders;
    public $inProgressOrders;
    public $completedOrders;
    public $cancelledOrders;

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->pendingOrders = JobOrder::where('status', 'pending')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->inProgressOrders = JobOrder::where('status', 'in_progress')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->completedOrders = JobOrder::where('status', 'completed')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->cancelledOrders = JobOrder::whereIn('status', ['cancelled'])
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($jobId, $newStatus)
    {
        $job = JobOrder::find($jobId);
        if ($job && in_array($newStatus, ['pending', 'in_progress', 'completed'])) {
            $job->update(['status' => $newStatus]);
            $this->loadOrders();
            session()->flash('success', 'Order status updated successfully!');
        }
    }

    public function render()
    {
        return view('livewire.order-board', [
            'pendingOrders' => $this->pendingOrders,
            'inProgressOrders' => $this->inProgressOrders,
            'completedOrders' => $this->completedOrders,
            'cancelledOrders' => $this->cancelledOrders,
        ])->layout('layouts.app');
    }
}
