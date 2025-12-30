<?php

namespace App\Livewire;

use App\Models\JobOrder;
use Livewire\Component;

class JobBoard extends Component
{
    public $pendingJobs;
    public $inProgressJobs;
    public $completedJobs;

    public function mount()
    {
        $this->loadJobs();
    }

    public function loadJobs()
    {
        $this->pendingJobs = JobOrder::where('status', 'pending')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->inProgressJobs = JobOrder::where('status', 'in_progress')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->completedJobs = JobOrder::where('status', 'completed')
            ->with('order.customer')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($jobId, $newStatus)
    {
        $job = JobOrder::find($jobId);
        if ($job && in_array($newStatus, ['pending', 'in_progress', 'completed'])) {
            $job->update(['status' => $newStatus]);
            $this->loadJobs();
            session()->flash('success', 'Job status updated successfully!');
        }
    }

    public function render()
    {
        return view('livewire.job-board', [
            'pendingJobs' => $this->pendingJobs,
            'inProgressJobs' => $this->inProgressJobs,
            'completedJobs' => $this->completedJobs,
        ])->layout('layouts.app');
    }
}
