<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;

class OrderView extends Component
{
    public $orderId;
    public $order;
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $showPaymentForm = false;

    protected $rules = [
        'paymentAmount' => 'required|integer|min:1',
        'paymentMethod' => 'required|in:cash,card,bank_transfer,check',
    ];

    public function mount($id)
    {
        $this->orderId = $id;
        $this->order = Order::with('customer', 'orderItems', 'jobOrder', 'payments')->find($id);

        if (!$this->order) {
            abort(404);
        }
    }

    public function addPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|integer|min:1',
            'paymentMethod' => 'required|in:cash,card,bank_transfer,check',
        ]);

        // Validate payment doesn't exceed remaining balance
        $remainingBalance = $this->order->remaining_balance;
        if ($this->paymentAmount > $remainingBalance) {
            session()->flash('error', 'Payment amount exceeds remaining balance of ₱' . number_format($remainingBalance));
            return;
        }

        // Create payment record
        Payment::create([
            'order_id' => $this->order->id,
            'amount' => $this->paymentAmount,
            'method' => $this->paymentMethod,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Update order payment status
        $totalPaid = $this->order->total_paid + $this->paymentAmount;
        if ($totalPaid >= $this->order->total_amount) {
            $this->order->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $this->order->update(['payment_status' => 'partial']);
        }

        $this->paymentAmount = '';
        $this->showPaymentForm = false;
        $this->order = Order::with('customer', 'orderItems', 'jobOrder', 'payments')->find($this->orderId);

        session()->flash('success', 'Payment recorded successfully!');
    }

    public function updateOrderStatus($status)
    {
        $validator = Validator::make(['status' => $status], [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            session()->flash('error', 'Invalid status');
            return;
        }

        $this->order->update(['status' => $status]);
        $this->order = Order::with('customer', 'orderItems', 'jobOrder', 'payments')->find($this->orderId);

        session()->flash('success', 'Order status updated successfully!');
    }

    public function togglePaymentForm()
    {
        $this->showPaymentForm = !$this->showPaymentForm;
        if (!$this->showPaymentForm) {
            $this->paymentAmount = '';
        }
    }

    public function render()
    {
        return view('livewire.order-view', [
            'order' => $this->order,
        ])->layout('layouts.app');
    }
}
