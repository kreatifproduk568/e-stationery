<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::livewire('/dashboard', 'dashboard')
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Category
|--------------------------------------------------------------------------
|
| File:
| resources/views/components/pages/categories/⚡create.blade.php
|
| Component:
| pages.categories.create
|
*/

Route::livewire('/categories', 'categories')
    ->name('categories.index');

Route::livewire('/categories/create', 'pages.categories.create')
    ->name('categories.create');

Route::livewire('/categories/{category}/edit', 'pages.categories.edit')
    ->name('categories.edit');


/*
|--------------------------------------------------------------------------
| Brand
|--------------------------------------------------------------------------
|
| File:
| resources/views/components/pages/brands/⚡create.blade.php
|
| Component:
| pages.brands.create
|
*/

Route::livewire('/brands', 'brands')
    ->name('brands.index');

Route::livewire('/brands/create', 'pages.brands.create')
    ->name('brands.create');

Route::livewire('/brands/{brand}/edit', 'pages.brands.edit')
    ->name('brands.edit');


/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
|
| File:
| resources/views/components/pages/products/⚡create.blade.php
|
| Component:
| pages.products.create
|
*/

Route::livewire('/products', 'products')
    ->name('products.index');

Route::livewire('/products/create', 'pages.products.create')
    ->name('products.create');

Route::livewire('/products/{product}/edit', 'pages.products.edit')
    ->name('products.edit');

/*
|--------------------------------------------------------------------------
| Product Variant
|--------------------------------------------------------------------------
*/

Route::livewire(
    '/products/{product}/variants/{variant}/edit',
    'pages.products.variants.edit'
)->name('products.variants.edit');



Route::livewire('/exam01', 'exam01');
Route::livewire('/exam02', 'exam02');
Route::livewire('/exam03', 'exam03');
Route::livewire('/exam04', 'exam04');