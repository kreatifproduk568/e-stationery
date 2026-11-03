<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Product Create
|--------------------------------------------------------------------------
|
| Component:
| pages.products.create
|
| File:
| resources/views/components/pages/products/⚡create.blade.php
|
*/

new  class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Product Information
    |--------------------------------------------------------------------------
    */

    public string $category_id = '';

    public string $brand_id = '';

    public string $sku = '';

    public string $name = '';

    public string $slug = '';

    public string $description = '';


    /*
    |--------------------------------------------------------------------------
    | Product Status
    |--------------------------------------------------------------------------
    */

    public bool $is_active = true;

    public bool $is_featured = false;


    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'uuid',
                'exists:categories,id',
            ],

            'brand_id' => [
                'required',
                'uuid',
                'exists:brands,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:products,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

            'is_featured' => [
                'boolean',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */

    protected function messages(): array
    {
        return [
            'category_id.required' => 'Category wajib dipilih.',
            'category_id.uuid' => 'Category tidak valid.',
            'category_id.exists' => 'Category tidak ditemukan.',

            'brand_id.required' => 'Brand wajib dipilih.',
            'brand_id.uuid' => 'Brand tidak valid.',
            'brand_id.exists' => 'Brand tidak ditemukan.',

            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 100 karakter.',
            'sku.unique' => 'SKU sudah digunakan.',

            'name.required' => 'Nama product wajib diisi.',
            'name.max' => 'Nama product maksimal 255 karakter.',

            'slug.required' => 'Slug wajib diisi.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan.',

            'description.string' => 'Description harus berupa teks.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Auto Generate Slug
    |--------------------------------------------------------------------------
    |
    | Ketika Product Name berubah,
    | slug akan dibuat otomatis.
    |
    */

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Product
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        /*
        | Validasi seluruh input.
        */
        $validated = $this->validate();

        /*
        | Simpan Product.
        |
        | Price, Cost Price, dan Stock TIDAK disimpan
        | di tabel products.
        */
        Product::create($validated);

        /*
        | Flash message.
        */
        session()->flash(
            'success',
            'Product berhasil ditambahkan.'
        );

        /*
        | Kembali ke Product List.
        */
        $this->redirectRoute(
            'products.index',
            navigate: true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(): void
    {
        $this->redirectRoute(
            'products.index',
            navigate: true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        /*
        | Ambil Category untuk dropdown.
        */
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        /*
        | Ambil Brand untuk dropdown.
        */
        $brands = Brand::query()
            ->orderBy('name')
            ->get();

        /*
        | Kirim data ke Blade.
        */
        return $this->view([
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }
};
?>

<div class="space-y-6 p-6">

    {{-- ======================================================
        BREADCRUMB
    ======================================================= --}}

    <div>
        <div class="text-xs text-gray-500">
            Products
            <span class="mx-1">/</span>
            Create
        </div>

        <h1 class="mt-1 text-xl font-semibold text-gray-900">
            Tambah Product
        </h1>

        <p class="text-sm text-gray-500">
            Tambahkan product stationery baru.
        </p>
    </div>


    {{-- ======================================================
        FORM
    ======================================================= --}}

    <form
        wire:submit="save"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
    >

        {{-- ==================================================
            INFORMASI PRODUCT
        =================================================== --}}

        <div class="p-6">

            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900">
                    Informasi Product
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Masukkan informasi utama product.
                </p>
            </div>


            {{-- CATEGORY + BRAND --}}

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- Category --}}

                <div>
                    <label
                        for="category_id"
                        class="mb-1.5 block text-xs font-medium text-gray-700"
                    >
                        Category
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="category_id"
                        wire:model="category_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">
                            -- Pilih Category --
                        </option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Brand --}}

                <div>
                    <label
                        for="brand_id"
                        class="mb-1.5 block text-xs font-medium text-gray-700"
                    >
                        Brand
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="brand_id"
                        wire:model="brand_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">
                            -- Pilih Brand --
                        </option>

                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('brand_id')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>


            {{-- SKU + PRODUCT NAME --}}

            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- SKU --}}

                <div>
                    <label
                        for="sku"
                        class="mb-1.5 block text-xs font-medium text-gray-700"
                    >
                        SKU
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="sku"
                        type="text"
                        wire:model="sku"
                        placeholder="Contoh: PILOT-G2-BLACK"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    <p class="mt-1 text-xs text-gray-400">
                        Kode unik untuk setiap product.
                    </p>

                    @error('sku')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Product Name --}}

                <div>
                    <label
                        for="name"
                        class="mb-1.5 block text-xs font-medium text-gray-700"
                    >
                        Product Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        wire:model.live="name"
                        placeholder="Contoh: Pilot G2 Gel Pen Black"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    @error('name')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>


            {{-- SLUG --}}

            <div class="mt-5">

                <label
                    for="slug"
                    class="mb-1.5 block text-xs font-medium text-gray-700"
                >
                    Slug
                    <span class="text-red-500">*</span>
                </label>

                <input
                    id="slug"
                    type="text"
                    wire:model="slug"
                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <p class="mt-1 text-xs text-gray-400">
                    Slug dibuat otomatis berdasarkan nama product.
                </p>

                @error('slug')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- DESCRIPTION --}}

            <div class="mt-5">

                <label
                    for="description"
                    class="mb-1.5 block text-xs font-medium text-gray-700"
                >
                    Description
                </label>

                <textarea
                    id="description"
                    wire:model="description"
                    rows="4"
                    placeholder="Deskripsi product..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>

                @error('description')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ==================================================
            STATUS PRODUCT
        =================================================== --}}

        <div class="border-t border-gray-200 p-6">

            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900">
                    Status Product
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Atur status dan tampilan product.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- ACTIVE --}}

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">

                    <input
                        type="checkbox"
                        wire:model="is_active"
                        class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >

                    <div>
                        <div class="text-sm font-medium text-gray-800">
                            Product Active
                        </div>

                        <p class="mt-1 text-xs text-gray-400">
                            Product dapat ditampilkan dan digunakan dalam transaksi.
                        </p>
                    </div>

                </label>


                {{-- FEATURED --}}

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">

                    <input
                        type="checkbox"
                        wire:model="is_featured"
                        class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >

                    <div>
                        <div class="text-sm font-medium text-gray-800">
                            Featured Product
                        </div>

                        <p class="mt-1 text-xs text-gray-400">
                            Tandai product sebagai product unggulan.
                        </p>
                    </div>

                </label>

            </div>

        </div>


        {{-- ==================================================
            ACTION
        =================================================== --}}

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">

            {{-- Cancel --}}

            <button
                type="button"
                wire:click="cancel"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Batal
            </button>


            {{-- Save --}}

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span wire:loading.remove>
                    Simpan Product
                </span>

                <span wire:loading>
                    Menyimpan...
                </span>
            </button>

        </div>

    </form>

</div>