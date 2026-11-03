<?php

use Illuminate\Support\Facades\Route;

Route::livewire('category-demo','category-demo')->name('category.demo');
Route::livewire('product-demo','product-demo')->name('product.demo');

Route::livewire('category-demo/tambah','pages.categories.tambah')->name('category.tambah');

Route::livewire('/categories/create', 'pages.categories.create')
    ->name('categories.create');


// ========================================
// GUEST
// ========================================

Route::middleware('guest')->group(function () {

    Route::livewire('/login', 'pages.auth.login')
        ->name('login');

});


// ========================================
// AUTHENTICATED
// ========================================

Route::middleware('auth')->group(function () {

    Route::livewire('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::livewire('/categories', 'categories')
        ->name('categories.index');

    Route::livewire('/categories/create', 'pages.categories.create')
    ->name('categories.create');

    Route::livewire('/categories/{category}/edit', 'pages.categories.edit')
    ->name('categories.edit');

    Route::livewire('/brands', 'brands')
        ->name('brands.index');

    Route::livewire('/brands/create', 'pages.brands.create')
    ->name('brands.create');
    
    Route::livewire('/brands/{brand}/edit', 'pages.brands.edit')
    ->name('brands.edit');


    Route::livewire('/products', 'products')
        ->name('products.index');

    Route::livewire('/products/create', 'pages.products.create')
    ->name('products.create');

    Route::livewire('/products/{product}/edit', 'pages.products.edit')
    ->name('products.edit');      
    
    Route::livewire('/users', 'users')
    ->name('users.index');

    Route::livewire('/users/create', 'pages.users.create')
    ->name('users.create');

    Route::livewire('/users/{user}/edit', 'pages.users.edit')
    ->name('users.edit');

    Route::livewire('/roles', 'roles')
    ->middleware('permission:roles.view')
    ->name('roles.index');

        Route::livewire('/roles/create', 'pages.roles.create')
    ->middleware('permission:roles.create')
    ->name('roles.create');

    Route::livewire('/roles/{role}/edit', 'pages.roles.edit')
    ->middleware('permission:roles.update')
    ->name('roles.edit');

    Route::livewire('/roles/{role}', 'pages.roles.show')
    ->middleware('permission:roles.view')
    ->name('roles.show');
});