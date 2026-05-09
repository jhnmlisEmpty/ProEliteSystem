<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UpholsteryOrder;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class OrderView extends Component
{
    public $orderId;
    public $order;
    
    // Payment properties
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $paymentNote = '';
    public $showPaymentForm = false;

    // Edit mode properties
    public $isEditMode = false;
    public $editCustomerName = '';

    // Status change
    public $newStatus = '';
    public $statusOptions = ['pending', 'in_progress', 'for_installation', 'completed', 'cancelled'];
    public $showStatusDropdown = false;

    // Payment status change
    public $newPaymentStatus = '';
    public $paymentStatusOptions = ['unpaid', 'partial', 'paid'];

    protected $rules = [
        'paymentAmount' => 'required|integer|min:1',
        'paymentMethod' => 'required|in:cash,bank_transfer,credit_card,gcash',
        'paymentNote' => 'nullable|string|max:500',
    ];

    protected $editRules = [
        'editCustomerName' => 'required|string|min:2',
    ];

    public function mount($id)
    {
        $this->orderId = $id;
        $this->loadOrder();
        $this->initializeEditFields();
    }

    private function loadOrder()
    {
        $this->order = Order::with([
            'customer',
            'orderItems.product',
            'orderItems.service',
            'orderItems.upholstery',
            'orderItems.vip',
            'payments',
            'expenses',
            'serviceAssignments.employee',
            'serviceAssignments.service',
        ])->find($this->orderId);

        if (!$this->order) {
            abort(404);
        }
    }

    private function initializeEditFields()
    {
        $this->editCustomerName = $this->order->customer_name;
        $this->newStatus = $this->order->status;
        $this->newPaymentStatus = $this->order->payment_status;
    }

    public function render()
    {
        return view('livewire.order-view', [
            'remainingBalance' => $this->getRemainingBalance(),
            'totalPaid' => $this->getTotalPaid(),
        ])->layout('layouts.app');
    }

    /**
     * PAYMENT MANAGEMENT
     */
    public function togglePaymentForm()
    {
        $this->showPaymentForm = !$this->showPaymentForm;
        if (!$this->showPaymentForm) {
            $this->resetPaymentForm();
        }
    }

    public function addPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|integer|min:1',
            'paymentMethod' => 'required|in:cash,bank_transfer,credit_card,gcash',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        $remainingBalance = $this->getRemainingBalance();
        if ($this->paymentAmount > $remainingBalance) {
            $this->addError('paymentAmount', 'Payment amount exceeds remaining balance of ₱' . number_format($remainingBalance));
            return;
        }

        try {
            DB::transaction(function () {
                Payment::create([
                    'order_id' => $this->order->id,
                    'amount' => $this->paymentAmount,
                    'method' => $this->paymentMethod,
                    'reference' => $this->paymentNote,
                    'paid_at' => now(),
                ]);

                // Update order payment status
                $this->updatePaymentStatus();

                // Update upholstery balance if order has upholstery
                $this->updateUpholsteryBalance();
            });

            session()->flash('success', 'Payment of ₱' . number_format($this->paymentAmount) . ' recorded successfully!');
            $this->resetPaymentForm();
            $this->loadOrder();
        } catch (\Exception $e) {
            $this->addError('submit', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    private function updateUpholsteryBalance()
    {
        // Get all upholstery items for this order
        $upholsteryItems = $this->order->orderItems()
            ->where('upholstery_id', '!=', null)
            ->with('upholstery')
            ->get();

        if ($upholsteryItems->isEmpty()) {
            return;
        }

        // Calculate total paid (payment already created in DB, so getTotalPaid includes it)
        $totalPaid = $this->getTotalPaid();
        $orderTotalGross = $this->order->total_gross;
        $orderTotalAmount = $this->order->total_amount; // After discount

        // Update each upholstery record's balance proportionally
        foreach ($upholsteryItems as $item) {
            if ($item->upholstery) {
                // Calculate this upholstery item's proportion of the order
                $upholsteryProportion = $orderTotalGross > 0 
                    ? $item->total_price / $orderTotalGross 
                    : 0;

                // Calculate upholstery amount after discount
                $upholsteryAfterDiscount = $upholsteryProportion * $orderTotalAmount;

                // Calculate how much has been paid towards this upholstery
                $upholsteryPaidAmount = $upholsteryProportion * $totalPaid;

                // Calculate remaining balance
                $upholsteryBalance = max(0, $upholsteryAfterDiscount - $upholsteryPaidAmount);

                $item->upholstery->update([
                    'balance' => (int) round($upholsteryBalance),
                    'downpayment' => (int) round($upholsteryPaidAmount),
                ]);
            }
        }
    }

    private function updatePaymentStatus()
    {
        $totalPaid = $this->getTotalPaid();
        $totalAmount = $this->order->total_amount;

        if ($totalPaid >= $totalAmount) {
            $this->order->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $this->order->update(['payment_status' => 'partial']);
        }
    }

    private function resetPaymentForm()
    {
        $this->paymentAmount = '';
        $this->paymentMethod = 'cash';
        $this->paymentNote = '';
        $this->showPaymentForm = false;
    }

    /**
     * STATUS MANAGEMENT
     */
    public function changeStatus($status = null)
    {
        // Use provided status parameter or fall back to newStatus property
        $statusToSet = $status ?? $this->newStatus;
        
        if ($statusToSet === $this->order->status) {
            session()->flash('error', 'Order is already in ' . ucfirst(str_replace('_', ' ', $statusToSet)) . ' status.');
            return;
        }

        try {
            $this->order->update(['status' => $statusToSet]);
            $this->newStatus = $statusToSet;
            session()->flash('success', 'Order status updated to ' . ucfirst($this->newStatus) . '!');
            $this->loadOrder();
        } catch (\Exception $e) {
            $this->addError('status', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * PAYMENT STATUS MANAGEMENT
     */
    public function changePaymentStatus($paymentStatus = null)
    {
        // Use provided status parameter or fall back to newPaymentStatus property
        $paymentStatusToSet = $paymentStatus ?? $this->newPaymentStatus;
        
        if ($paymentStatusToSet === $this->order->payment_status) {
            session()->flash('error', 'Order payment is already ' . ucfirst($paymentStatusToSet) . '.');
            return;
        }

        try {
            $this->order->update(['payment_status' => $paymentStatusToSet]);
            $this->newPaymentStatus = $paymentStatusToSet;
            session()->flash('success', 'Payment status updated to ' . ucfirst($this->newPaymentStatus) . '!');
            $this->loadOrder();
        } catch (\Exception $e) {
            $this->addError('payment_status', 'Failed to update payment status: ' . $e->getMessage());
        }
    }

    /**
     * EDIT MODE
     */
    public function enterEditMode()
    {
        $this->isEditMode = true;
        $this->initializeEditFields();
    }

    public function cancelEdit()
    {
        $this->isEditMode = false;
        $this->resetErrorBag();
    }

    public function saveOrder()
    {
        $this->validate($this->editRules);

        try {
            $this->order->update([
                'customer_name' => $this->editCustomerName,
            ]);

            session()->flash('success', 'Order details updated successfully!');
            $this->isEditMode = false;
            $this->loadOrder();
        } catch (\Exception $e) {
            $this->addError('submit', 'Failed to save order: ' . $e->getMessage());
        }
    }

    /**
     * HELPER METHODS
     */
    private function getRemainingBalance(): int
    {
        return max(0, $this->order->total_amount - $this->getTotalPaid());
    }

    private function getTotalPaid(): int
    {
        return (int) $this->order->payments()->sum('amount');
    }
}
