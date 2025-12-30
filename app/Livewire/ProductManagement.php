<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManagement extends Component
{
    use WithPagination;

    // UI State
    public $search = '';
    public $typeFilter = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }



    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();

        session()->flash('success', 'Product deleted successfully!');
        $this->resetPage();
    }



    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query();

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by type
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $products = $query
            ->orderByRaw('stock_qty <= alert_limit desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.product-management', [
            'products' => $products
        ])->layout('layouts.app');
    }
}
