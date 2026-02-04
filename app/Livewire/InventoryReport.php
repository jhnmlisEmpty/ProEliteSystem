<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class InventoryReport extends Component
{
    public array $availableItems = [];
    public array $soldOutItems = [];
    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->isAdmin = auth()->user()?->isAdmin();
        $this->loadInventory();
    }

    private function loadInventory(): void
    {
        $query = Product::query();

        // Apply branch filtering if not admin
        if (!$this->isAdmin) {
            $query->where('branch_id', auth()->user()?->branch_id);
        }

        $products = $query->orderBy('name')->get();

        $this->availableItems = $products
            ->filter(fn($product) => $product->stock_qty > 0)
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'stock_qty' => $product->stock_qty,
                'buy_price' => $product->buy_price,
                'sell_price' => $product->sell_price,
                'alert_limit' => $product->alert_limit,
                'branch' => $product->branch?->name ?? 'N/A',
                'inventory_value' => $product->stock_qty * $product->buy_price,
            ])
            ->values()
            ->toArray();

        $this->soldOutItems = $products
            ->filter(fn($product) => $product->stock_qty <= 0)
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'stock_qty' => $product->stock_qty,
                'buy_price' => $product->buy_price,
                'sell_price' => $product->sell_price,
                'alert_limit' => $product->alert_limit,
                'branch' => $product->branch?->name ?? 'N/A',
                'inventory_value' => $product->stock_qty * $product->buy_price,
            ])
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.inventory-report')->layout('layouts.app');
    }
}
