<?php

namespace App\Livewire;

use App\Models\Branch;
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

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:branches,code',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:50',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage branches.');
        }

        $branches = Branch::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->withCount('users', 'products', 'customers', 'orders')
            ->latest()
            ->paginate(10);

        return view('livewire.branch-management', [
            'branches' => $branches,
        ]);
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
        $this->reset(['name', 'code', 'address', 'phone', 'is_active']);
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
        $this->reset(['editingId', 'name', 'code', 'address', 'phone', 'is_active']);
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id);
        
        // Check if branch has users
        if ($branch->users()->count() > 0) {
            session()->flash('error', 'Cannot delete branch with assigned users!');
            return;
        }

        $branch->delete();
        session()->flash('success', 'Branch deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_active' => !$branch->is_active]);
        
        session()->flash('success', 'Branch status updated!');
    }

    public function cancel()
    {
        $this->reset(['editingId', 'name', 'code', 'address', 'phone', 'is_active']);
    }
}
