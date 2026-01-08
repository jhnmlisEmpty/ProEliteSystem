<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToBranch
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToBranch(): void
    {
        // Automatically set branch_id on create
        static::creating(function (Model $model) {
            if (!$model->branch_id && Auth::check() && Auth::user()->branch_id) {
                $model->branch_id = Auth::user()->branch_id;
            }
        });

        // Automatically scope queries by branch (unless user is admin)
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // If user is not admin, filter by their branch
                if (!$user->isAdmin()) {
                    $builder->where($builder->getModel()->getTable() . '.branch_id', $user->branch_id);
                }
            }
        });
    }

    /**
     * Get the branch that owns the model.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope to query records from a specific branch.
     */
    public function scopeForBranch(Builder $query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to query records from all branches (admin only).
     */
    public function scopeAllBranches(Builder $query)
    {
        return $query->withoutGlobalScope('branch');
    }
}
