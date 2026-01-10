<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Employee;
use Livewire\Component;

class BranchForm extends Component
{
    public ?Branch $branch = null;

    public $name = '';
    public $code = '';
    public $address = '';
    public $phone = '';
    public $is_active = true;

    public $employee_ids = [];
    public $employeeCandidates = [];

    public function mount(?int $id = null): void
    {
        // Load available employees whose linked user role is employee
        $this->employeeCandidates = Employee::with('user')
            ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
            ->orderBy('name')
            ->get();

        if ($id) {
            $this->branch = Branch::findOrFail($id);
            $this->name = $this->branch->name;
            $this->code = $this->branch->code ?? '';
            $this->address = $this->branch->address ?? '';
            $this->phone = $this->branch->phone ?? '';
            $this->is_active = (bool) ($this->branch->is_active ?? true);

            // Preselect all assigned employees
            $this->employee_ids = $this->branch->employees()
                ->pluck('employees.id')
                ->toArray();
        }
    }

    protected function rules(): array
    {
        $codeRule = 'nullable|string|max:50';

        return [
            'name' => 'required|string|max:255',
            'code' => $codeRule,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->branch) {
            $this->branch->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Update assigned employees
            $selectedIds = $data['employee_ids'] ?? [];

            // Remove unselected employees from this branch
            Employee::where('branch_id', $this->branch->id)
                ->whereNotIn('id', $selectedIds)
                ->update(['branch_id' => null]);

            // Add selected employees to this branch
            if (!empty($selectedIds)) {
                Employee::whereIn('id', $selectedIds)->update(['branch_id' => $this->branch->id]);
            }

            session()->flash('success', 'Branch updated successfully!');
        } else {
            $branch = Branch::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Assign selected employees to the new branch
            $selectedIds = $data['employee_ids'] ?? [];
            if (!empty($selectedIds)) {
                Employee::whereIn('id', $selectedIds)->update(['branch_id' => $branch->id]);
            }

            session()->flash('success', 'Branch created successfully!');
        }

        return redirect()->route('branches.index');
    }

    public function toggleEmployee($employeeId)
    {
        if (in_array($employeeId, $this->employee_ids)) {
            $this->employee_ids = array_values(array_diff($this->employee_ids, [$employeeId]));
        } else {
            $this->employee_ids[] = $employeeId;
        }
    }

    public function cancel()
    {
        return redirect()->route('branches.index');
    }

    public function render()
    {
        return view('livewire.branch-form')->layout('layouts.app');
    }
}
