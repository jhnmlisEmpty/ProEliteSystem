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
    public $branchFilter = '';
    public $itemTypeFilter = '';
    public $view = 'table'; // 'table' or 'kanban'
    public $tableTab = 'all'; // 'all', 'pending', 'in_progress', 'completed', 'cancelled'
    public $showAllPending = false; // Toggle for showing all pending orders in kanban
    public $showAllInProgress = false; // Toggle for showing all in progress orders in kanban
    public $showAllCompleted = false; // Toggle for showing all completed orders in kanban

    public function setView($view)
    {
        $this->view = $view;
    }

    public function toggleShowAllPending()
    {
        $this->showAllPending = !$this->showAllPending;
    }

    public function toggleShowAllInProgress()
    {
        $this->showAllInProgress = !$this->showAllInProgress;
    }

    public function toggleShowAllCompleted()
    {
        $this->showAllCompleted = !$this->showAllCompleted;
    }

    public function setTableTab($tab)
    {
        $this->tableTab = $tab;
        $this->resetPage();
    }

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

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->paymentFilter = '';
        $this->branchFilter = '';
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

    public function updateOrderStatus($id, $status)
    {
        if (empty($status)) {
            return;
        }

        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        session()->flash('success', 'Order status updated to ' . ucwords(str_replace('_', ' ', $status)) . '.');
    }

    public function render()
    {
        $baseQuery = Order::with(['customer', 'orderItems', 'expenses'])
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
            ->when($this->branchFilter, function ($q) {
                $q->where('branch_id', $this->branchFilter);
            })
            ->when($this->itemTypeFilter, function ($q) {
                $itemType = $this->itemTypeFilter;
                $q->whereHas('orderItems', function ($subQuery) use ($itemType) {
                    if ($itemType === 'product') {
                        $subQuery->whereNotNull('product_id');
                    } elseif ($itemType === 'service') {
                        $subQuery->whereNotNull('service_id');
                    } elseif ($itemType === 'upholstery') {
                        $subQuery->whereNotNull('upholstery_id');
                    } elseif ($itemType === 'vip') {
                        $subQuery->whereNotNull('vip_id');
                    }
                });
            });

        // For table view with tabs
        if ($this->view === 'table') {
            $query = clone $baseQuery;
            
            if ($this->tableTab !== 'all') {
                $query->where('status', $this->tableTab);
            }
            
            $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        } else {
            // For kanban view, we need all data without pagination
            $orders = collect();
        }

        // Get orders by status for kanban view
        $pendingQuery = $baseQuery->clone()->where('status', 'pending')->orderBy('created_at', 'desc');
        $pendingTotal = $pendingQuery->count();
        $pendingOrders = $this->showAllPending ? $pendingQuery->get() : $pendingQuery->limit(10)->get();
        
        $inProgressQuery = $baseQuery->clone()->where('status', 'in_progress')->orderBy('created_at', 'desc');
        $inProgressTotal = $inProgressQuery->count();
        $inProgressOrders = $this->showAllInProgress ? $inProgressQuery->get() : $inProgressQuery->limit(10)->get();
        
        $completedQuery = $baseQuery->clone()->where('status', 'completed')->orderBy('created_at', 'desc');
        $completedTotal = $completedQuery->count();
        $completedOrders = $this->showAllCompleted ? $completedQuery->get() : $completedQuery->limit(10)->get();

        $branches = \App\Models\Branch::where('is_active', true)->get();
        $canFilterBranch = in_array(auth()->user()->role, ['admin', 'manager']);

        return view('livewire.order-management', [
            'orders' => $orders,
            'pendingOrders' => $pendingOrders,
            'pendingTotal' => $pendingTotal,
            'inProgressOrders' => $inProgressOrders,
            'inProgressTotal' => $inProgressTotal,
            'completedOrders' => $completedOrders,
            'completedTotal' => $completedTotal,
            'branches' => $branches,
            'canFilterBranch' => $canFilterBranch,
        ])->layout('layouts.app');
    }
}
