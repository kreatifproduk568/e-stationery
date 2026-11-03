<?php

/*
|--------------------------------------------------------------------------
| PRODUCTS - LIVEWIRE 4 SINGLE FILE COMPONENT
|--------------------------------------------------------------------------
|
| File:
| resources/views/components/⚡products.blade.php
|
| Fitur:
|
| - Product List
| - Search
| - Filter Category
| - Filter Brand
| - Filter Status
| - Filter Featured
| - Pagination
| - Edit Product
| - Delete Product
| - Dependency Check
| - Custom Delete Modal
| - Flash Message
|
|--------------------------------------------------------------------------
*/

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;


/*
|--------------------------------------------------------------------------
| LIVEWIRE COMPONENT
|--------------------------------------------------------------------------
*/

new class extends Component
{
    use WithPagination;


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    #[Url]
    public string $search = '';


    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    public string $category = '';

    public string $brand = '';

    public string $status = '';

    public string $featured = '';


    /*
    |--------------------------------------------------------------------------
    | DELETE MODAL
    |--------------------------------------------------------------------------
    */

    public bool $showDeleteModal = false;

    public ?string $deleteId = null;

    public string $deleteName = '';


    /*
    |--------------------------------------------------------------------------
    | DEPENDENCY INFORMATION
    |--------------------------------------------------------------------------
    |
    | Menyimpan jumlah child data yang dimiliki Product.
    |
    */

    public int $deleteVariantCount = 0;

    public int $deleteImageCount = 0;


    /*
    |--------------------------------------------------------------------------
    | DEPENDENCY ERROR
    |--------------------------------------------------------------------------
    |
    | true:
    | Product tidak boleh dihapus karena masih mempunyai
    | Variant atau Image.
    |
    */

    public bool $hasDeleteDependency = false;


    /*
    |--------------------------------------------------------------------------
    | RESET PAGINATION
    |--------------------------------------------------------------------------
    */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }


    public function updatingCategory(): void
    {
        $this->resetPage();
    }


    public function updatingBrand(): void
    {
        $this->resetPage();
    }


    public function updatingStatus(): void
    {
        $this->resetPage();
    }


    public function updatingFeatured(): void
    {
        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRM DELETE
    |--------------------------------------------------------------------------
    |
    | Method ini tidak langsung menghapus Product.
    |
    | Pertama-tama kita periksa apakah Product masih memiliki:
    |
    | - Product Variant
    | - Product Image
    |
    */

    public function confirmDelete(string $id): void
    {
        /*
        |--------------------------------------------------------------------------
        | Cari Product
        |--------------------------------------------------------------------------
        */

        $product = Product::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Simpan Product Information
        |--------------------------------------------------------------------------
        */

        $this->deleteId = $product->id;

        $this->deleteName = $product->name;


        /*
        |--------------------------------------------------------------------------
        | Hitung Product Variant
        |--------------------------------------------------------------------------
        */

        $this->deleteVariantCount = $product
            ->variants()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Hitung Product Image
        |--------------------------------------------------------------------------
        */

        $this->deleteImageCount = $product
            ->images()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Tentukan apakah Product masih mempunyai dependency
        |--------------------------------------------------------------------------
        */

        $this->hasDeleteDependency =
            $this->deleteVariantCount > 0 ||
            $this->deleteImageCount > 0;


        /*
        |--------------------------------------------------------------------------
        | Buka Modal
        |--------------------------------------------------------------------------
        */

        $this->showDeleteModal = true;
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL DELETE
    |--------------------------------------------------------------------------
    */

    public function cancelDelete(): void
    {
        $this->resetDeleteState();
    }


    /*
    |--------------------------------------------------------------------------
    | RESET DELETE STATE
    |--------------------------------------------------------------------------
    */

    private function resetDeleteState(): void
    {
        $this->showDeleteModal = false;

        $this->deleteId = null;

        $this->deleteName = '';

        $this->deleteVariantCount = 0;

        $this->deleteImageCount = 0;

        $this->hasDeleteDependency = false;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    |
    | Product hanya boleh dihapus jika:
    |
    | Variant = 0
    | Image   = 0
    |
    */

    public function deleteProduct(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pastikan Product ID tersedia
        |--------------------------------------------------------------------------
        */

        if (!$this->deleteId) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil Product terbaru dari database
        |--------------------------------------------------------------------------
        |
        | Kita cek ulang dependency saat tombol Delete ditekan.
        | Ini penting agar data tidak berubah di antara:
        |
        | confirmDelete()
        |
        | dan
        |
        | deleteProduct()
        |
        */

        $product = Product::findOrFail(
            $this->deleteId
        );


        /*
        |--------------------------------------------------------------------------
        | Cek Variant
        |--------------------------------------------------------------------------
        */

        $variantCount = $product
            ->variants()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Cek Image
        |--------------------------------------------------------------------------
        */

        $imageCount = $product
            ->images()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Jangan Delete jika masih mempunyai dependency
        |--------------------------------------------------------------------------
        */

        if (
            $variantCount > 0 ||
            $imageCount > 0
        ) {

            /*
            |--------------------------------------------------------------------------
            | Update informasi modal
            |--------------------------------------------------------------------------
            */

            $this->deleteVariantCount = $variantCount;

            $this->deleteImageCount = $imageCount;

            $this->hasDeleteDependency = true;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Simpan Nama Product
        |--------------------------------------------------------------------------
        */

        $productName = $product->name;


        /*
        |--------------------------------------------------------------------------
        | Hapus Product
        |--------------------------------------------------------------------------
        |
        | Pada titik ini Product sudah tidak mempunyai:
        |
        | - Variant
        | - Image
        |
        | sehingga aman untuk dihapus.
        |
        */

        $product->delete();


        /*
        |--------------------------------------------------------------------------
        | Reset Modal
        |--------------------------------------------------------------------------
        */

        $this->resetDeleteState();


        /*
        |--------------------------------------------------------------------------
        | Flash Message
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            "Product '{$productName}' berhasil dihapus."
        );


        /*
        |--------------------------------------------------------------------------
        | Reset Pagination
        |--------------------------------------------------------------------------
        */

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCT QUERY
        |--------------------------------------------------------------------------
        */

        $products = Product::query()

            /*
            |--------------------------------------------------------------------------
            | Eager Loading
            |--------------------------------------------------------------------------
            */

            ->with([
                'category',
                'brand',
            ])


            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            ->when(
                $this->search,
                function ($query) {

                    $query->where(function ($query) {

                        $query
                            ->where(
                                'name',
                                'like',
                                '%' . $this->search . '%'
                            )
                            ->orWhere(
                                'sku',
                                'like',
                                '%' . $this->search . '%'
                            );

                    });

                }
            )


            /*
            |--------------------------------------------------------------------------
            | FILTER CATEGORY
            |--------------------------------------------------------------------------
            */

            ->when(
                $this->category,
                function ($query) {

                    $query->where(
                        'category_id',
                        $this->category
                    );

                }
            )


            /*
            |--------------------------------------------------------------------------
            | FILTER BRAND
            |--------------------------------------------------------------------------
            */

            ->when(
                $this->brand,
                function ($query) {

                    $query->where(
                        'brand_id',
                        $this->brand
                    );

                }
            )


            /*
            |--------------------------------------------------------------------------
            | FILTER STATUS
            |--------------------------------------------------------------------------
            */

            ->when(
                $this->status !== '',
                function ($query) {

                    $query->where(
                        'is_active',
                        $this->status === '1'
                    );

                }
            )


            /*
            |--------------------------------------------------------------------------
            | FILTER FEATURED
            |--------------------------------------------------------------------------
            */

            ->when(
                $this->featured !== '',
                function ($query) {

                    $query->where(
                        'is_featured',
                        $this->featured === '1'
                    );

                }
            )


            /*
            |--------------------------------------------------------------------------
            | SORTING
            |--------------------------------------------------------------------------
            */

            ->latest()


            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            ->paginate(3);


        /*
        |--------------------------------------------------------------------------
        | CATEGORY LIST
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | BRAND LIST
        |--------------------------------------------------------------------------
        */

        $brands = Brand::query()
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return $this->view([
            'products'   => $products,
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }
};


?>


<div class="space-y-6 p-6">


    {{-- ================================================================
         HEADER
         ================================================================ --}}

    <div
        class="flex flex-col gap-4
               sm:flex-row
               sm:items-center
               sm:justify-between"
    >

        <div>

            <h1 class="text-2xl font-bold text-gray-900">
                Products
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data produk stationery.
            </p>

        </div>


        {{-- TAMBAH PRODUCT --}}

        <a
            href="{{ route('products.create') }}"
            wire:navigate
            class="inline-flex
                   items-center
                   justify-center
                   rounded-lg
                   bg-slate-900
                   px-4 py-2.5
                   text-sm
                   font-semibold
                   text-white
                   shadow-sm
                   transition
                   hover:bg-slate-800
                   focus:outline-none
                   focus:ring-2
                   focus:ring-slate-500
                   focus:ring-offset-2"
        >

            <svg
                class="mr-2 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                />

            </svg>

            Tambah Product

        </a>

    </div>


    {{-- ================================================================
         FLASH MESSAGE
         ================================================================ --}}

    @if (session()->has('success'))

        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="flex
                   items-center
                   justify-between
                   rounded-lg
                   border
                   border-green-200
                   bg-green-50
                   px-4 py-3
                   text-sm
                   text-green-700"
        >

            <div class="flex items-center gap-2">

                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />

                </svg>

                <span>
                    {{ session('success') }}
                </span>

            </div>


            <button
                type="button"
                @click="show = false"
                class="text-lg
                       font-bold
                       text-green-600
                       hover:text-green-800"
            >
                &times;
            </button>

        </div>

    @endif


    {{-- ================================================================
         FILTER CARD
         ================================================================ --}}

    <div
        class="rounded-xl
               bg-white
               p-5
               shadow-sm
               ring-1
               ring-gray-200"
    >

        <div
            class="grid
                   grid-cols-1
                   gap-4
                   md:grid-cols-2
                   lg:grid-cols-6"
        >


            {{-- SEARCH --}}

            <div class="lg:col-span-2">

                <label
                    for="search"
                    class="mb-1
                           block
                           text-sm
                           font-medium
                           text-gray-700"
                >
                    Search Product
                </label>


                <div class="relative">

                    <div
                        class="pointer-events-none
                               absolute
                               inset-y-0
                               left-0
                               flex
                               items-center
                               pl-3"
                    >

                        <svg
                            class="h-5 w-5
                                   text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                            />

                        </svg>

                    </div>


                    <input
                        id="search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari SKU atau nama product..."
                        class="w-full
                               rounded-lg
                               border-gray-300
                               py-2.5
                               pl-10
                               pr-4
                               shadow-sm
                               focus:border-indigo-500
                               focus:ring-indigo-500"
                    >

                </div>

            </div>


            {{-- CATEGORY --}}

            <div>

                <label
                    for="category"
                    class="mb-1
                           block
                           text-sm
                           font-medium
                           text-gray-700"
                >
                    Category
                </label>


                <select
                    id="category"
                    wire:model.live="category"
                    class="w-full
                           rounded-lg
                           border-gray-300
                           py-2.5
                           shadow-sm
                           focus:border-indigo-500
                           focus:ring-indigo-500"
                >

                    <option value="">
                        Semua Category
                    </option>


                    @foreach ($categories as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- BRAND --}}

            <div>

                <label
                    for="brand"
                    class="mb-1
                           block
                           text-sm
                           font-medium
                           text-gray-700"
                >
                    Brand
                </label>


                <select
                    id="brand"
                    wire:model.live="brand"
                    class="w-full
                           rounded-lg
                           border-gray-300
                           py-2.5
                           shadow-sm
                           focus:border-indigo-500
                           focus:ring-indigo-500"
                >

                    <option value="">
                        Semua Brand
                    </option>


                    @foreach ($brands as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- STATUS --}}

            <div>

                <label
                    for="status"
                    class="mb-1
                           block
                           text-sm
                           font-medium
                           text-gray-700"
                >
                    Status
                </label>


                <select
                    id="status"
                    wire:model.live="status"
                    class="w-full
                           rounded-lg
                           border-gray-300
                           py-2.5
                           shadow-sm
                           focus:border-indigo-500
                           focus:ring-indigo-500"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <option value="1">
                        Active
                    </option>

                    <option value="0">
                        Inactive
                    </option>

                </select>

            </div>


            {{-- FEATURED --}}

            <div>

                <label
                    for="featured"
                    class="mb-1
                           block
                           text-sm
                           font-medium
                           text-gray-700"
                >
                    Featured
                </label>


                <select
                    id="featured"
                    wire:model.live="featured"
                    class="w-full
                           rounded-lg
                           border-gray-300
                           py-2.5
                           shadow-sm
                           focus:border-indigo-500
                           focus:ring-indigo-500"
                >

                    <option value="">
                        Semua Product
                    </option>

                    <option value="1">
                        Featured
                    </option>

                    <option value="0">
                        Tidak Featured
                    </option>

                </select>

            </div>

        </div>

    </div>


    {{-- ================================================================
         PRODUCT TABLE
         ================================================================ --}}

    <div
        class="overflow-hidden
               rounded-xl
               bg-white
               shadow-sm
               ring-1
               ring-gray-200"
    >


        {{-- LOADING --}}

        <div
            wire:loading
            wire:target="search,category,brand,status,featured"
            class="border-b
                   border-indigo-100
                   bg-indigo-50
                   px-6 py-2
                   text-sm
                   text-indigo-600"
        >
            Memuat data product...
        </div>


        <div class="overflow-x-auto">

            <table
                class="min-w-full
                       divide-y
                       divide-gray-200"
            >

                {{-- TABLE HEADER --}}

                <thead class="bg-gray-50">

                    <tr>

                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            Product
                        </th>


                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            SKU
                        </th>


                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            Category
                        </th>


                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            Brand
                        </th>


                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            Status
                        </th>


                        <th
                            class="px-6 py-3
                                   text-right
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-gray-500"
                        >
                            Action
                        </th>

                    </tr>

                </thead>


                {{-- TABLE BODY --}}

                <tbody
                    class="divide-y
                           divide-gray-200
                           bg-white"
                >

                    @forelse ($products as $product)

                        <tr
                            wire:key="product-{{ $product->id }}"
                            class="transition
                                   hover:bg-gray-50"
                        >

                            {{-- PRODUCT --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4"
                            >

                                <div
                                    class="font-semibold
                                           text-gray-900"
                                >
                                    {{ $product->name }}
                                </div>


                                {{-- FEATURED --}}

                                @if ($product->is_featured)

                                    <span
                                        class="mt-1
                                               inline-flex
                                               rounded-full
                                               bg-yellow-100
                                               px-2 py-0.5
                                               text-xs
                                               font-medium
                                               text-yellow-700"
                                    >
                                        Featured
                                    </span>

                                @endif

                            </td>


                            {{-- SKU --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4
                                       text-sm
                                       text-gray-600"
                            >
                                {{ $product->sku }}
                            </td>


                            {{-- CATEGORY --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4
                                       text-sm
                                       text-gray-600"
                            >
                                {{ $product->category?->name ?? '-' }}
                            </td>


                            {{-- BRAND --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4
                                       text-sm
                                       text-gray-600"
                            >
                                {{ $product->brand?->name ?? '-' }}
                            </td>


                            {{-- STATUS --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4
                                       text-center"
                            >

                                @if ($product->is_active)

                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-green-100
                                               px-2.5 py-1
                                               text-xs
                                               font-medium
                                               text-green-700"
                                    >
                                        Active
                                    </span>

                                @else

                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-gray-100
                                               px-2.5 py-1
                                               text-xs
                                               font-medium
                                               text-gray-600"
                                    >
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}

                            <td
                                class="whitespace-nowrap
                                       px-6 py-4
                                       text-right
                                       text-sm"
                            >

                                <div
                                    class="flex
                                           justify-end
                                           gap-2"
                                >

                                    {{-- EDIT --}}

                                    <a
                                        href="{{ route(
                                            'products.edit',
                                            $product->id
                                        ) }}"
                                        wire:navigate
                                        class="rounded-lg
                                               px-3 py-1.5
                                               font-medium
                                               text-indigo-600
                                               transition
                                               hover:bg-indigo-50"
                                    >
                                        Edit
                                    </a>


                                    {{-- DELETE --}}

                                    <button
                                        type="button"
                                        wire:click="confirmDelete('{{ $product->id }}')"
                                        wire:loading.attr="disabled"
                                        class="rounded-lg
                                               px-3 py-1.5
                                               font-medium
                                               text-red-600
                                               transition
                                               hover:bg-red-50
                                               disabled:cursor-not-allowed
                                               disabled:opacity-50"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>


                    @empty

                        {{-- EMPTY STATE --}}

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-12
                                       text-center"
                            >

                                <div
                                    class="flex
                                           flex-col
                                           items-center
                                           justify-center
                                           text-gray-400"
                                >

                                    <svg
                                        class="h-14 w-14"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />

                                    </svg>


                                    <p
                                        class="mt-3
                                               text-sm
                                               font-semibold
                                               text-gray-600"
                                    >
                                        Belum ada product.
                                    </p>


                                    <p class="mt-1 text-sm">
                                        Tambahkan product pertama Anda.
                                    </p>


                                    <a
                                        href="{{ route('products.create') }}"
                                        wire:navigate
                                        class="mt-4
                                               inline-flex
                                               items-center
                                               rounded-lg
                                               bg-slate-900
                                               px-4 py-2
                                               text-sm
                                               font-semibold
                                               text-white
                                               hover:bg-slate-800"
                                    >
                                        + Tambah Product
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        @if ($products->hasPages())

            <div
                class="border-t
                       border-gray-200
                       px-6 py-4"
            >
                {{ $products->links() }}
            </div>

        @endif

    </div>


    {{-- =================================================================
         DELETE PRODUCT MODAL
         ================================================================= --}}

    @if ($showDeleteModal)

        <div
            class="fixed
                   inset-0
                   z-50
                   flex
                   items-center
                   justify-center
                   bg-black/50
                   px-4"
            wire:keydown.escape="cancelDelete"
        >

            <div
                class="w-full
                       max-w-lg
                       overflow-hidden
                       rounded-2xl
                       bg-white
                       shadow-2xl"
            >


                {{-- ====================================================
                     MODAL HEADER
                     ==================================================== --}}

                <div
                    class="flex
                           items-start
                           gap-4
                           border-b
                           px-6 py-5"
                >

                    {{-- ICON --}}

                    <div
                        class="flex
                               h-11 w-11
                               shrink-0
                               items-center
                               justify-center
                               rounded-full
                               bg-red-100
                               text-red-600"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.84 21h16.32a2 2 0 001.73-3.14l-8.18-14a2 2 0 00-3.42 0z"
                            />

                        </svg>

                    </div>


                    {{-- TITLE --}}

                    <div>

                        <h3
                            class="text-lg
                                   font-semibold
                                   text-gray-900"
                        >

                            @if ($hasDeleteDependency)

                                Product Tidak Dapat Dihapus

                            @else

                                Hapus Product

                            @endif

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   text-gray-500"
                        >

                            @if ($hasDeleteDependency)

                                Masih terdapat data yang terkait.

                            @else

                                Konfirmasi penghapusan data.

                            @endif

                        </p>

                    </div>

                </div>


                {{-- ====================================================
                     MODAL BODY
                     ==================================================== --}}

                <div class="px-6 py-6">


                    {{-- PRODUCT NAME --}}

                    <p
                        class="text-sm
                               text-gray-600"
                    >
                        Product:
                    </p>


                    <div
                        class="mt-2
                               rounded-lg
                               bg-gray-50
                               px-4 py-3"
                    >

                        <p
                            class="text-sm
                                   font-semibold
                                   text-gray-900"
                        >
                            {{ $deleteName }}
                        </p>

                    </div>


                    {{-- =================================================
                         DEPENDENCY WARNING
                         ================================================= --}}

                    @if ($hasDeleteDependency)

                        <div
                            class="mt-5
                                   rounded-lg
                                   border
                                   border-amber-200
                                   bg-amber-50
                                   p-4"
                        >

                            <div class="flex gap-3">

                                {{-- ICON --}}

                                <svg
                                    class="mt-0.5
                                           h-5 w-5
                                           shrink-0
                                           text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.84 21h16.32a2 2 0 001.73-3.14l-8.18-14a2 2 0 001.73-3.14z"
                                    />

                                </svg>


                                <div>

                                    <p
                                        class="text-sm
                                               font-semibold
                                               text-amber-800"
                                    >
                                        Product masih memiliki:
                                    </p>


                                    <ul
                                        class="mt-2
                                               space-y-1
                                               text-sm
                                               text-amber-700"
                                    >

                                        {{-- VARIANT --}}

                                        @if ($deleteVariantCount > 0)

                                            <li>
                                                •
                                                {{ $deleteVariantCount }}
                                                Product Variant
                                            </li>

                                        @endif


                                        {{-- IMAGE --}}

                                        @if ($deleteImageCount > 0)

                                            <li>
                                                •
                                                {{ $deleteImageCount }}
                                                Product Image
                                            </li>

                                        @endif

                                    </ul>

                                </div>

                            </div>

                        </div>


                        {{-- INSTRUCTION --}}

                        <p
                            class="mt-4
                                   text-sm
                                   font-medium
                                   text-gray-700"
                        >
                            Silakan hapus Variant dan Image tersebut
                            terlebih dahulu sebelum menghapus Product.
                        </p>


                    @else

                        {{-- =================================================
                             SAFE DELETE WARNING
                             ================================================= --}}

                        <div
                            class="mt-5
                                   rounded-lg
                                   border
                                   border-red-200
                                   bg-red-50
                                   p-4"
                        >

                            <p
                                class="text-sm
                                       font-semibold
                                       text-red-700"
                            >
                                Product ini tidak memiliki Variant
                                atau Image.
                            </p>


                            <p
                                class="mt-2
                                       text-sm
                                       text-red-600"
                            >
                                Product dapat dihapus dan tindakan ini
                                tidak dapat dibatalkan.
                            </p>

                        </div>

                    @endif

                </div>


                {{-- ====================================================
                     MODAL FOOTER
                     ==================================================== --}}

                <div
                    class="flex
                           justify-end
                           gap-3
                           border-t
                           bg-gray-50
                           px-6 py-4"
                >

                    {{-- BATAL / TUTUP --}}

                    <button
                        type="button"
                        wire:click="cancelDelete"
                        wire:loading.attr="disabled"
                        class="rounded-lg
                               border
                               border-gray-300
                               bg-white
                               px-4 py-2.5
                               text-sm
                               font-medium
                               text-gray-700
                               hover:bg-gray-50
                               disabled:cursor-not-allowed
                               disabled:opacity-50"
                    >

                        @if ($hasDeleteDependency)
                            Tutup
                        @else
                            Batal
                        @endif

                    </button>


                    {{-- =================================================
                         DELETE BUTTON
                         ================================================= --}}

                    @if (!$hasDeleteDependency)

                        <button
                            type="button"
                            wire:click="deleteProduct"
                            wire:loading.attr="disabled"
                            wire:target="deleteProduct"
                            class="rounded-lg
                                   bg-red-600
                                   px-4 py-2.5
                                   text-sm
                                   font-medium
                                   text-white
                                   hover:bg-red-700
                                   disabled:cursor-not-allowed
                                   disabled:opacity-50"
                        >

                            <span
                                wire:loading.remove
                                wire:target="deleteProduct"
                            >
                                Hapus Product
                            </span>


                            <span
                                wire:loading
                                wire:target="deleteProduct"
                            >
                                Menghapus...
                            </span>

                        </button>

                    @endif

                </div>

            </div>

        </div>

    @endif

</div>