<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class BranchManagement extends Component
{
    use WithPagination;

    public $name = '';
    public $code = '';
    public $address = '';
    public $phone = '';
    public $is_active = true;
    public $editingId = null;
    public $search = '';
    public $showForm = false;

    // Employee properties
    public $selectedBranchId = null;
    public $showEmployeeForm = false;
    public $employeeName = '';
    public $employeePosition = '';
    public $employeePhone = '';
    public $employeeEmail = '';
    public $editingEmployeeId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:branches,code',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:50',
        'is_active' => 'boolean',
    ];

    protected $employeeRules = [
        'employeeName' => 'required|string|max:255',
        'employeePosition' => 'required|string|max:255',
        'employeePhone' => 'nullable|string|max:20',
        'employeeEmail' => 'nullable|email|max:255',
    ];

    public function render()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage branches.');
        }

        $branches = Branch::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->withCount('users', 'products', 'customers', 'orders', 'employees')
            ->with('users', 'employees')
            ->latest()
            ->paginate(10);

        $selectedBranch = $this->selectedBranchId ? Branch::with('employees')->find($this->selectedBranchId) : null;

        return view('livewire.branch-management', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
        ]);
    }

    // BRANCH MANAGEMENT METHODS
    public function openBranchForm()
    {
        $this->showForm = true;
        $this->reset(['editingId', 'name', 'code', 'address', 'phone']);
        $this->is_active = true;
    }

    public function save()
    {
        $this->validate();

        Branch::create([
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'address' => $this->address,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Branch created successfully!');
        $this->showForm = false;
        $this->reset(['name', 'code', 'address', 'phone']);
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $this->editingId = $branch->id;
        $this->name = $branch->name;
        $this->code = $branch->code;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->is_active = $branch->is_active;
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $this->editingId,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $branch = Branch::findOrFail($this->editingId);
        $branch->update([
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'address' => $this->address,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Branch updated successfully!');
        $this->showForm = false;
        $this->reset(['editingId', 'name', 'code', 'address', 'phone']);
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->delete();
        session()->flash('success', 'Branch deleted successfully!');
        $this->selectedBranchId = null;
    }

    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_active' => !$branch->is_active]);
        
        session()->flash('success', 'Branch status updated!');
    }

    public function cancelBranchForm()
    {
        $this->showForm = false;
        $this->reset(['editingId', 'name', 'code', 'address', 'phone']);
    }

    // EMPLOYEE MANAGEMENT METHODS
    public function selectBranch($branchId)
    {
        $this->selectedBranchId = $branchId;
        $this->showEmployeeForm = false;
    }

    public function openEmployeeForm()
    {
        $this->showEmployeeForm = true;
        $this->reset(['editingEmployeeId', 'employeeName', 'employeePosition', 'employeePhone', 'employeeEmail']);
    }

    public function saveEmployee()
    {
        $this->validate($this->employeeRules);

        Employee::create([
            'name' => $this->employeeName,
            'position' => $this->employeePosition,
            'phone' => $this->employeePhone,
            'email' => $this->employeeEmail,
            'branch_id' => $this->selectedBranchId,
        ]);

        session()->flash('success', 'Employee added successfully!');
        $this->showEmployeeForm = false;
        $this->reset(['employeeName', 'employeePosition', 'employeePhone', 'employeeEmail']);
    }

    public function editEmployee($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $this->editingEmployeeId = $employee->id;
        $this->employeeName = $employee->name;
        $this->employeePosition = $employee->position;
        $this->employeePhone = $employee->phone;
        $this->employeeEmail = $employee->email;
        $this->showEmployeeForm = true;
    }

    public function updateEmployee()
    {
        $this->validate($this->employeeRules);

        $employee = Employee::findOrFail($this->editingEmployeeId);
        $employee->update([
            'name' => $this->employeeName,
            'position' => $this->employeePosition,
            'phone' => $this->employeePhone,
            'email' => $this->employeeEmail,
        ]);

        session()->flash('success', 'Employee updated successfully!');
        $this->showEmployeeForm = false;
        $this->reset(['editingEmployeeId', 'employeeName', 'employeePosition', 'employeePhone', 'employeeEmail']);
    }

    public function deleteEmployee($employeeId)
    {
        Employee::findOrFail($employeeId)->delete();
        session()->flash('success', 'Employee deleted successfully!');
    }

    public function cancelEmployeeForm()
    {
        $this->showEmployeeForm = false;
        $this->reset(['editingEmployeeId', 'employeeName', 'employeePosition', 'employeePhone', 'employeeEmail']);
    }
}
