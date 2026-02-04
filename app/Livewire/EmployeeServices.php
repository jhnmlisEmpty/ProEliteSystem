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

    public function togglePaidStatus($assignmentId)
    {
        if (!$this->employee) return;

        // Get the specific assignment
        $assignment = ServiceAssignment::where('id', $assignmentId)
            ->where('employee_id', $this->employee->id)
            ->first();

        if (!$assignment) return;

        // Toggle the payment status for this individual assignment
        $currentStatus = $assignment->payment_status ?? 'unpaid';
        $newStatus = $currentStatus === 'paid' ? 'unpaid' : 'paid';
        
        $assignment->update(['payment_status' => $newStatus]);
        
        // Refresh the component to reflect the change
        $this->dispatch('assignmentUpdated');
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
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($assignment) => $assignment->created_at->format('Y-m-d'));

        // Sort dates descending (newest first)
        $assignmentsByDate = $serviceAssignments->sortKeysDesc();

        return view('livewire.employee-services', [
            'user' => $this->user,
            'assignmentsByDate' => $assignmentsByDate,
        ])->layout('layouts.app');
    }
}
