<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|string|max:255|unique:products,sku')]
    public $sku = '';
    
    #[Validate('nullable|image|max:2048')]
    public $image;
    
    #[Validate('required|in:retail,material')]
    public $type = 'retail';
    
    #[Validate('required|numeric|min:0')]
    public $stock_qty = 0;
    
    #[Validate('required|numeric|min:0')]
    public $buy_price = 0;
    
    #[Validate('nullable|numeric|min:0')]
    public $sell_price;
    
    #[Validate('required|numeric|min:0')]
    public $alert_limit = 10;
    
    #[Validate('required|exists:branches,id')]
    public $branch_id;

    public function mount($id = null)
    {

        // Set default branch based on user role
        if (!$id) {
            $user = auth()->user();
            if ($user->role === 'user') {
                $this->branch_id = $user->branch_id;
            }
        }
        
        if ($id) {
            $this->product = Product::findOrFail($id);
            $this->name = $this->product->name;
            $this->sku = $this->product->sku;
            $this->type = $this->product->type;
            $this->stock_qty = $this->product->stock_qty;
            $this->buy_price = $this->product->buy_price;
            $this->sell_price = $this->product->sell_price;
            $this->alert_limit = $this->product->alert_limit;
            $this->branch_id = $this->product->branch_id;
        }
    }

    public function save()
    {
        if ($this->product) {
            $rules = [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:255|unique:products,sku,' . $this->product->id,
                'image' => 'nullable|image|max:2048',
                'type' => 'required|in:retail,material',
                'stock_qty' => 'required|numeric|min:0',
                'buy_price' => 'required|numeric|min:0',
                'sell_price' => 'nullable|numeric|min:0',
                'alert_limit' => 'required|numeric|min:0',
                'branch_id' => 'required|exists:branches,id',
            ];
            
            $validated = $this->validate($rules);

            if ($this->image) {
                if ($this->product->image) {
                    \Storage::disk('public')->delete($this->product->image);
                }
                $validated['image'] = $this->image->store('products', 'public');
            } else {
                unset($validated['image']);
            }

            $this->product->update($validated);
            session()->flash('success', 'Product updated successfully!');
        } else {
            $validated = $this->validate();

            if ($this->image) {
                $validated['image'] = $this->image->store('products', 'public');
            }

            Product::create($validated);
            session()->flash('success', 'Product created successfully!');
        }

        return $this->redirect('/products', navigate: true);
    }

    public function cancel()
    {
        return $this->redirect('/products', navigate: true);
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $user = auth()->user();
        $canSelectBranch = in_array($user->role, ['admin', 'manager', 'order_creator']);
        
        return view('livewire.product-form', [
            'branches' => $branches,
            'canSelectBranch' => $canSelectBranch,
        ])->layout('layouts.app');
    }
}
