<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        session()->flash('success', 'User deleted successfully!');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query()->with('branch');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($this->perPage);

        return view('livewire.user-management', [
            'users' => $users
        ])->layout('layouts.app');
    }
}
