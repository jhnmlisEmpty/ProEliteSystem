<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductLog;
use Livewire\Component;
use Livewire\WithPagination;

class ProductLogs extends Component
{
    use WithPagination;

    public Product $product;
    public $perPage = 15;

    protected $paginationTheme = 'tailwind';

    public function mount(int $id): void
    {
        $this->product = Product::findOrFail($id);
    }

    public function back()
    {
        return redirect()->route('products.index');
    }

    public function render()
    {
        $logs = ProductLog::where('product_id', $this->product->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.product-logs', [
            'logs' => $logs
        ])->layout('layouts.app');
    }
}
