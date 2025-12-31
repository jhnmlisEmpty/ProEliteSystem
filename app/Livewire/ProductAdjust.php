<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductLog;
use Livewire\Component;

class ProductAdjust extends Component
{
    public Product $product;
    public string $direction = 'increase';
    public ?string $reason = null;
    public ?float $change_amount = null;

    protected $rules = [
        'direction' => 'required|in:increase,decrease',
        'change_amount' => 'required|integer|min:1',
        'reason' => 'required|string|max:255',
    ];

    public function mount(int $id): void
    {
        $this->product = Product::findOrFail($id);
    }

    public function save()
    {
        $data = $this->validate();

        $delta = abs((int) $data['change_amount']);
        $delta = $data['direction'] === 'decrease' ? -$delta : $delta;

        // Prevent negative stock
        if ($data['direction'] === 'decrease' && $this->product->stock_qty + $delta < 0) {
            $this->addError('change_amount', 'Cannot decrease stock by more than available quantity (' . $this->product->stock_qty . ' units).');
            return;
        }

        $this->product->stock_qty = $this->product->stock_qty + $delta;
        $this->product->save();

        ProductLog::create([
            'product_id' => $this->product->id,
            'change_amount' => $delta,
            'reference_id' => 'PL' . random_int(100000, 999999),
            'reason' => $data['reason'],
        ]);

        session()->flash('success', 'Stock adjusted successfully.');

        return redirect()->route('products.index');
    }

    public function cancel()
    {
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product-adjust')->layout('layouts.app');
    }
}
