<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;





Route::get('/', function () {
    return view('welcome');
});
Route::livewire('/categories/create', 'categories.create')
    ->name('categories.create');

Route::livewire('/categories/{category}/edit', 'categories.edit')
    ->name('categories.edit');
        
Route::resource('categories',CategoryController::class) ->except(['index','create','edit']);
Route::livewire('/categories', 'categories')
    ->name('categories.index');


Route::livewire('/products', 'products')
    ->name('products.index');

Route::livewire('/products/create', 'pages::products.create')
    ->name('products.create');

Route::livewire('/products/{product}/edit', 'pages::products.edit')
    ->name('products.edit');


Route::resource('brands', BrandController::class);
Route::resource('products', ProductController::class);
Route::resource('users', UserController::class);

Route::view('/login', 'auth.login')
    ->name('login');

Route::livewire('/dashboard', 'dashboard')
    ->name('dashboard');


// Route::livewire('/categories/create', 'categories.create')
//     ->name('categories.create');

// Route::livewire('/categories/create', 'pages::categories.create')
//     ->name('categories.create');


