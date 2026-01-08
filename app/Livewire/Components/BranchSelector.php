<?php

namespace App\Livewire\Components;

use App\Models\Branch;
use Livewire\Component;

class BranchSelector extends Component
{
    public $selectedBranchId = null;
    public $branches = [];
    public $canAccessAll = false;

    public function mount()
    {
        $this->canAccessAll = auth()->user()->canAccessAllBranches();
        
        if ($this->canAccessAll) {
            $this->branches = Branch::active()->get();
            // Set to "All Branches" by default for admin
            $this->selectedBranchId = 'all';
        } else {
            // Regular users see only their branch
            $this->selectedBranchId = auth()->user()->branch_id;
        }
    }

    public function updatedSelectedBranchId($value)
    {
        // Emit event to notify other components about branch change
        $this->dispatch('branchChanged', branchId: $value);
    }

    public function render()
    {
        return view('livewire.components.branch-selector');
    }
}
