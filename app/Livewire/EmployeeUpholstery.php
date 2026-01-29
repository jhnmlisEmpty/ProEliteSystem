<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Employee;
use App\Models\UpholsteryAssignment;
use App\Models\UpholsteryOrder;
use Illuminate\Support\Collection;
use Livewire\Component;

class EmployeeUpholstery extends Component
{
    public ?User $user = null;
    public ?Employee $employee = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function togglePaidStatus($upholsteryId)
    {
        if (!$this->employee) return;

        // Get all assignments for this employee and upholstery
        $assignments = UpholsteryAssignment::where('employee_id', $this->employee->id)
            ->where('upholstery_id', $upholsteryId)
            ->get();

        if ($assignments->isEmpty()) return;

        // Determine new status (toggle based on first assignment)
        $current = $assignments->first()->payment_status ?? 'unpaid';
        $newStatus = $current === 'paid' ? 'unpaid' : 'paid';

        // Update all assignments for this employee/upholstery
        UpholsteryAssignment::where('employee_id', $this->employee->id)
            ->where('upholstery_id', $upholsteryId)
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
            return view('livewire.employee-upholstery')->layout('layouts.app');
        }

        // Get all upholstery assignments for this employee
        $upholsteryAssignments = UpholsteryAssignment::where('employee_id', $this->employee->id)
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->with(['upholstery.order.customer'])
            ->get()
            ->groupBy('upholstery_id');

        // Build upholstery summary with earnings and co-workers
        $upholsterySummary = collect();
        
        foreach ($upholsteryAssignments as $upholsteryId => $assignments) {
            $upholsteryOrder = $assignments->first()->upholstery;
            
            // Calculate order value (use balance + downpayment as total, or add a total_price accessor if needed)
            $orderValue = $upholsteryOrder ? (($upholsteryOrder->balance ?? 0) + ($upholsteryOrder->downpayment ?? 0)) : 0;

            // Get other employees assigned to this upholstery order
            $otherEmployees = UpholsteryAssignment::where('upholstery_id', $upholsteryId)
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

            // Get payment status from the first assignment (assuming all have the same status)
            $payment_status = $assignments->first()->payment_status ?? 'unpaid';

            $upholsterySummary->push([
                'upholsteryOrder' => $upholsteryOrder,
                'assignmentCount' => $assignments->count(),
                'orderValue' => $orderValue,
                'otherEmployeesCount' => $otherEmployees->count(),
                'otherEmployees' => $otherEmployees,
                'assignments' => $assignments,
                'payment_status' => $payment_status,
            ]);
        }

        return view('livewire.employee-upholstery', [
            'user' => $this->user,
            'upholsterySummary' => $upholsterySummary,
        ])->layout('layouts.app');
    }
}
