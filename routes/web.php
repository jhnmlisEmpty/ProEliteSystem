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
use App\Livewire\CustomerView;
use App\Livewire\Dashboard;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use App\Livewire\UserManagement;
use App\Livewire\UserForm;
use App\Livewire\BranchManagement;
use App\Livewire\BranchForm;
use App\Livewire\ExpenseManagement;
use App\Livewire\ExpenseForm;
use App\Livewire\EmployeeServices;
use App\Livewire\DailyReport;

// Guest routes
Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected routes
Route::middleware(['auth', 'denyEmployee'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard.index');
    // Orders routes - accessible to order_creator role
    Route::get('/orders', OrderManagement::class)->name('orders.index');
    Route::get('/orders/create', CreateOrder::class)->name('orders.create');
    Route::get('/orders/{id}', OrderView::class)->name('orders.view');
    Route::get('/orders/{id}/edit', OrderEdit::class)->name('orders.edit')->middleware('admin');
    

    // All other routes - blocked for order_creator
    Route::middleware('denyOrderCreator')->group(function () {
        
        Route::get('/reports/daily', DailyReport::class)->name('reports.daily');

        Route::get('/products', ProductManagement::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create')->middleware('admin');
        Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit')->middleware('admin');
        Route::get('/products/{id}/adjust', ProductAdjust::class)->name('products.adjust')->middleware('admin');
        Route::get('/products/{id}/logs', ProductLogs::class)->name('products.logs');

        Route::get('/services', ServiceManagement::class)->name('services.index');
        Route::get('/services/create', ServiceForm::class)->name('services.create');
        Route::get('/services/{id}/edit', ServiceForm::class)->name('services.edit')->middleware('admin');

        Route::get('/customers', CustomerManagement::class)->name('customers.index');
        Route::get('/customers/create', CustomerForm::class)->name('customers.create');
        Route::get('/customers/{id}', CustomerView::class)->name('customers.view');
        Route::get('/customers/{id}/edit', CustomerForm::class)->name('customers.edit')->middleware('admin');

        // Users
        Route::get('/users', UserManagement::class)->name('users.index')->middleware('admin');
        Route::get('/users/create', UserForm::class)->name('users.create')->middleware('admin');
        Route::get('/users/{id}/edit', UserForm::class)->name('users.edit')->middleware('admin');
        
        // Employee Services

        // Employee Services
        Route::get('/employees/{id}/services', EmployeeServices::class)->name('employees.services')->middleware('admin');
        // Employee Upholstery
        Route::get('/employees/{id}/upholstery', \App\Livewire\EmployeeUpholstery::class)->name('employees.upholstery')->middleware('admin');

        // Branches
        Route::get('/branches', BranchManagement::class)->name('branches.index')->middleware('admin');
        Route::get('/branches/create', BranchForm::class)->name('branches.create')->middleware('admin');
        Route::get('/branches/{id}/edit', BranchForm::class)->name('branches.edit')->middleware('admin');

        // Expenses
        Route::get('/expenses', ExpenseManagement::class)->name('expenses.index');
        Route::get('/expenses/create', ExpenseForm::class)->name('expenses.create');
        Route::get('/expenses/{id}/edit', ExpenseForm::class)->name('expenses.edit')->middleware('admin');
    });
});
