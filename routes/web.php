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
use App\Livewire\OrderManagement;
use App\Livewire\OrderView;
use App\Livewire\OrderEdit;
use App\Livewire\CreateOrder;
use App\Livewire\OrderBoard;
use App\Livewire\Dashboard;
use App\Livewire\BranchManagement;
// use App\Livewire\BranchUserManagement;
use App\Livewire\UserManagement;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;

// Guest routes
Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
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

    Route::get('/orders', OrderManagement::class)->name('orders.index');
    Route::get('/orders/create', CreateOrder::class)->name('orders.create');
    Route::get('/orders/{id}', OrderView::class)->name('orders.view');
    Route::get('/orders/{id}/edit', OrderEdit::class)->name('orders.edit');

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::get('/users', UserManagement::class)->name('users.index')->middleware('ongoing');
        Route::get('/branches', BranchManagement::class)->name('branches.index')->middleware('ongoing');
        
    });
});
