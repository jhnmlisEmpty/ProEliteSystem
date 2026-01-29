<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Employee;
use App\Models\ServiceAssignment;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Livewire\Component;

class EmployeeServices extends Component
{
    public ?User $user = null;
    public ?Employee $employee = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function togglePaidStatus($serviceId)
    {
        if (!$this->employee) return;

        // Get all assignments for this employee and service
        $assignments = ServiceAssignment::where('employee_id', $this->employee->id)
            ->where('service_id', $serviceId)
            ->get();

        if ($assignments->isEmpty()) return;

        // Determine new status (toggle based on first assignment)
        $current = $assignments->first()->payment_status ?? 'unpaid';
        $newStatus = $current === 'paid' ? 'unpaid' : 'paid';

        // Update all assignments for this employee/service
        ServiceAssignment::where('employee_id', $this->employee->id)
            ->where('service_id', $serviceId)
            ->update(['payment_status' => $newStatus]);
    }

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        // Load the user
        $this->user = User::findOrFail($id);

        // Verify it's an employee
        if ($this->user->role !== 'employee') {
            abort(403, 'This user is not an employee');
        }

        // Resolve the Employee model via user_id
        $this->employee = Employee::where('user_id', $this->user->id)->first();

        if (!$this->employee) {
            abort(404, 'Employee record not found for this user');
        }
    }

    public function clearFilters(): void
    {
        $this->startDate = null;
        $this->endDate = null;
    }

    public function render()
    {
        if (!$this->employee || !$this->user) {
            return view('livewire.employee-services')->layout('layouts.app');
        }

        // Get all service assignments for this employee
        $serviceAssignments = ServiceAssignment::where('employee_id', $this->employee->id)
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->with(['service', 'order', 'order.customer'])
            ->get()
            ->groupBy('service_id');

        // Build services summary with earnings and co-workers
        $servicesSummary = collect();
        
        foreach ($serviceAssignments as $serviceId => $assignments) {
            $service = $assignments->first()->service;
            
            // Calculate service value from order_items (total_price per service)
            $orderIds = $assignments->pluck('order_id');
            $serviceValue = OrderItem::whereIn('order_id', $orderIds)
                ->where('service_id', $serviceId)
                ->sum('total_price');

            // Get other employees assigned to this service
            $otherEmployees = ServiceAssignment::where('service_id', $serviceId)
                ->where('employee_id', '!=', $this->employee->id)
                ->with('employee')
                ->get()
                ->groupBy('employee_id')
                ->map(function ($group) {
                    return [
                        'name' => $group->first()->employee->name,
                        'count' => $group->count()
                    ];
                })
                ->values();

            // Get payment status from the first assignment (all should be the same for this employee/service)
            $paymentStatus = $assignments->first()->payment_status ?? 'unpaid';

            $servicesSummary->push([
                'service' => $service,
                'assignmentCount' => $assignments->count(),
                'serviceValue' => $serviceValue,
                'otherEmployeesCount' => $otherEmployees->count(),
                'otherEmployees' => $otherEmployees,
                'assignments' => $assignments,
                'payment_status' => $paymentStatus,
            ]);
        }

        return view('livewire.employee-services', [
            'user' => $this->user,
            'servicesSummary' => $servicesSummary,
        ])->layout('layouts.app');
    }
}
