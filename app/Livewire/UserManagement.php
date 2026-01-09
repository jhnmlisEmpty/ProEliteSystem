<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $branchFilter = '';
    public $perPage = 10;
    public $showForm = false;
    
    // User form properties
    public $editingId = null;
    public $userName = '';
    public $userEmail = '';
    public $userPassword = '';
    public $userPasswordConfirm = '';
    public $userRole = 'user';
    public $userBranchId = null;

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        $rules = [
            'userName' => 'required|string|max:255',
            'userRole' => 'required|in:user,manager,admin',
            'userBranchId' => 'nullable|exists:branches,id',
        ];

        if ($this->editingId) {
            $rules['userEmail'] = 'required|email|unique:users,email,' . $this->editingId;
            // Password is optional when editing
            if ($this->userPassword) {
                $rules['userPassword'] = 'required|min:6|confirmed';
            }
        } else {
            $rules['userEmail'] = 'required|email|unique:users,email';
            $rules['userPassword'] = 'required|min:6|confirmed';
        }

        return $rules;
    }

    public function mount()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage users.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function openUserForm()
    {
        $this->showForm = true;
        $this->reset(['editingId', 'userName', 'userEmail', 'userPassword', 'userPasswordConfirm', 'userRole', 'userBranchId']);
        $this->userRole = 'user';
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userBranchId = $user->branch_id;
        $this->userPassword = '';
        $this->userPasswordConfirm = '';
        $this->showForm = true;
    }

    public function saveUser()
    {
        $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'role' => $this->userRole,
                'branch_id' => $this->userBranchId,
            ]);

            if ($this->userPassword) {
                $user->update(['password' => Hash::make($this->userPassword)]);
            }

            session()->flash('success', 'User updated successfully!');
        } else {
            User::create([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'password' => Hash::make($this->userPassword),
                'role' => $this->userRole,
                'branch_id' => $this->userBranchId,
            ]);

            session()->flash('success', 'User created successfully!');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'userName', 'userEmail', 'userPassword', 'userPasswordConfirm', 'userRole', 'userBranchId']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting admin users
        if ($user->isAdmin()) {
            session()->flash('error', 'Cannot delete admin users!');
            return;
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Cannot delete your own account!');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');
        $this->resetPage();
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->reset(['editingId', 'userName', 'userEmail', 'userPassword', 'userPasswordConfirm', 'userRole', 'userBranchId']);
    }

    public function render()
    {
        $query = User::query();

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by role
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        // Filter by branch
        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        $users = $query
            ->with('branch')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $branches = Branch::where('is_active', true)->get();

        return view('livewire.user-management', [
            'users' => $users,
            'branches' => $branches,
        ])->layout('layouts.app');
    }
}
