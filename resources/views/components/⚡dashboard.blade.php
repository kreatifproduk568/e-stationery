<?php

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new  class extends Component
{   
    #[Computed]
    public function categoryCount()
    {
        return Category::count();
    }

    #[Computed]
    public function brandCount()
    {
        return Brand::count();
    }

    #[Computed]
    public function productCount()
    {
        return Product::count();
    }
    #[Computed]
    public function userCount()
    {
        return User::count();
    }
};
?>

<div class="space-y-6 p-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Dashboard
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Ringkasan data e-Stationery.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

        {{-- Categories --}}

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

            <p class="text-sm text-slate-500">
                Categories
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $this->categoryCount }}
            </p>

        </div>

        {{-- Brands --}}

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

            <p class="text-sm text-slate-500">
                Brands
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $this->brandCount }}
            </p>

        </div>


        {{-- Products --}}

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

            <p class="text-sm text-slate-500">
                Products
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $this->productCount }}
            </p>

        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

            <p class="text-sm text-slate-500">
                Users
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ $this->userCount }}
            </p>

        </div>

    </div>

</div>