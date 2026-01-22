<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CustomerView extends Component
{
    use WithPagination;

    public $customerId;
    public $customer;
    public $perPage = 10;

    // Payment form properties
    public $paymentOrderId = null;
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $paymentNote = '';
    public $showPaymentForm = [];

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'paymentAmount' => 'required|integer|min:1',
        'paymentMethod' => 'required|in:cash,bank_transfer,credit_card,gcash',
        'paymentNote' => 'nullable|string|max:500',
    ];

    public function mount($id)
    {
        $this->customerId = $id;
        $this->customer = Customer::findOrFail($id);
    }

    public function getRemainingBalanceProperty()
    {
        $totalOrders = $this->customer->orders()->sum('total_amount');
        $totalPayments = Payment::whereIn('order_id', $this->customer->orders()->pluck('id'))->sum('amount');
        
        return $totalOrders - $totalPayments;
    }

    public function getTotalOrdersProperty()
    {
        return $this->customer->orders()->count();
    }

    public function getTotalSpentProperty()
    {
        return $this->customer->orders()->sum('total_amount');
    }

    public function togglePaymentForm($orderId)
    {
        $orderId = (string) $orderId;
        if (isset($this->showPaymentForm[$orderId])) {
            $this->showPaymentForm[$orderId] = !$this->showPaymentForm[$orderId];
        } else {
            $this->showPaymentForm[$orderId] = true;
        }

        if (!($this->showPaymentForm[$orderId] ?? false)) {
            $this->resetPaymentForm();
        }
    }

    public function addPayment($orderId)
    {
        $this->paymentOrderId = $orderId;
        
        $this->validate([
            'paymentAmount' => 'required|integer|min:1',
            'paymentMethod' => 'required|in:cash,bank_transfer,credit_card,gcash',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        $order = $this->customer->orders()->find($orderId);
        if (!$order) {
            session()->flash('error', 'Order not found!');
            return;
        }

        $orderPayments = Payment::where('order_id', $orderId)->sum('amount');
        $orderBalance = $order->total_amount - $orderPayments;

        if ($this->paymentAmount > $orderBalance) {
            session()->flash('error', "Payment cannot exceed remaining balance of ₱" . number_format($orderBalance, 0));
            return;
        }

        DB::transaction(function () use ($orderId) {
            Payment::create([
                'order_id' => $orderId,
                'amount' => $this->paymentAmount,
                'method' => $this->paymentMethod,
                'reference' => $this->paymentNote,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $order = $this->customer->orders()->find($orderId);
            $totalOrderPayments = Payment::where('order_id', $orderId)->sum('amount');

            if ($totalOrderPayments >= $order->total_amount) {
                $order->update(['payment_status' => 'paid']);
            } elseif ($totalOrderPayments > 0) {
                $order->update(['payment_status' => 'partial']);
            }
        });

        session()->flash('success', 'Payment recorded successfully!');
        $this->resetPaymentForm();
        $this->showPaymentForm[(string)$orderId] = false;
    }

    private function resetPaymentForm()
    {
        $this->paymentAmount = '';
        $this->paymentMethod = 'cash';
        $this->paymentNote = '';
        $this->paymentOrderId = null;
    }

    public function render()
    {
        $orders = $this->customer->orders()
                                 ->with('orderItems')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate($this->perPage);

        return view('livewire.customer-view', [
            'orders' => $orders,
            'remainingBalance' => $this->remaining_balance,
            'totalOrders' => $this->total_orders,
            'totalSpent' => $this->total_spent,
        ])->layout('layouts.app');;
    }
}
