<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Branch;
use Livewire\Component;

class UserForm extends Component
{
    public ?User $user = null;

    public $name = '';
    public $email = '';
    public $role = 'user';
    public $branch_id = null;
    public $password = '';

    public array $roles = ['admin', 'manager', 'employee','order_creator'];
    public $branches;

    public function mount(?int $id = null): void
    {
        $this->branches = Branch::active()->orderBy('name')->get();

        if ($id) {
            $this->user = User::findOrFail($id);
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->role = $this->user->role ?? 'user';
            $this->branch_id = $this->user->branch_id;
        }
    }

    protected function rules(): array
    {
        $emailRule = 'required|email|unique:users,email';
        if ($this->user) {
            $emailRule = 'required|email|unique:users,email,' . $this->user->id;
        }

        $passwordRule = $this->user ? 'nullable|string|min:8' : 'required|string|min:8';

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'role' => 'required|in:admin,manager,employee,order_creator',
            'branch_id' => 'nullable|exists:branches,id',
            'password' => $passwordRule,
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // Ensure branch_id is null if empty string
        $branchId = !empty($data['branch_id']) ? $data['branch_id'] : null;

        if ($this->user) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'branch_id' => $branchId,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            $this->user->update($updateData);
            session()->flash('success', 'User updated successfully!');
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'branch_id' => $branchId,
                'password' => $data['password'],
            ]);
            session()->flash('success', 'User created successfully!');
        }

        return redirect()->route('users.index');
    }

    public function cancel()
    {
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user-form')->layout('layouts.app');
    }
}
