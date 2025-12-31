<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ProductManagement;
use App\Livewire\ProductForm;
use App\Livewire\ProductAdjust;
use App\Livewire\ProductLogs;
use App\Livewire\ServiceManagement;
use App\Livewire\ServiceForm;
use App\Livewire\CustomerManagement;
use App\Livewire\CustomerForm;
use App\Livewire\PointOfSale;
use App\Livewire\OrderManagement;
use App\Livewire\OrderView;
use App\Livewire\OrderEdit;
use App\Livewire\OrderBoard;
use App\Livewire\Dashboard;

Route::get('/', Dashboard::class)->name('dashboard.index');

Route::get('/products', ProductManagement::class)->name('products.index');
Route::get('/products/create', ProductForm::class)->name('products.create');
Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit');
Route::get('/products/{id}/adjust', ProductAdjust::class)->name('products.adjust');
Route::get('/products/{id}/logs', ProductLogs::class)->name('products.logs');

Route::get('/services', ServiceManagement::class)->name('services.index');
Route::get('/services/create', ServiceForm::class)->name('services.create');
Route::get('/services/{id}/edit', ServiceForm::class)->name('services.edit');

Route::get('/customers', CustomerManagement::class)->name('customers.index');
Route::get('/customers/create', CustomerForm::class)->name('customers.create');
Route::get('/customers/{id}/edit', CustomerForm::class)->name('customers.edit');

Route::get('/pos', PointOfSale::class)->name('pos.create');
Route::get('/orders', OrderManagement::class)->name('orders.index');
Route::get('/orders/{id}', OrderView::class)->name('orders.view');
Route::get('/orders/{id}/edit', OrderEdit::class)->name('orders.edit');
Route::get('/board', OrderBoard::class)->name('board.index');
