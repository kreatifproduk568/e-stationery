<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new  class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Data Utama
    |--------------------------------------------------------------------------
    */

    public Product $product;

    public ProductVariant $variant;


    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    public string $sku = '';

    public string $variant_name = '';

    public string $color = '';

    public string $material = '';

    public $price = '';

    public $cost_price = '';

    public int $stock = 0;


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    |
    | Mengambil product dan variant berdasarkan parameter route.
    |
    */

    public function mount(
        Product $product,
        ProductVariant $variant
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Pastikan variant memang milik product yang sedang dibuka
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $variant->product_id === $product->id,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Simpan object
        |--------------------------------------------------------------------------
        */

        $this->product = $product;

        $this->variant = $variant;


        /*
        |--------------------------------------------------------------------------
        | Isi form dari database
        |--------------------------------------------------------------------------
        */

        $this->sku = $variant->sku;

        $this->variant_name = $variant->variant_name;

        $this->color = $variant->color ?? '';

        $this->material = $variant->material ?? '';

        $this->price = $variant->price;

        $this->cost_price = $variant->cost_price;

        $this->stock = $variant->stock;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Variant
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $validated = $this->validate(
            [
                'sku' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('product_variants', 'sku')
                        ->ignore($this->variant->id),
                ],

                'variant_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'material' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'cost_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'stock' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'sku.required' =>
                    'SKU variant wajib diisi.',

                'sku.max' =>
                    'SKU maksimal 100 karakter.',

                'sku.unique' =>
                    'SKU tersebut sudah digunakan variant lain.',

                'variant_name.required' =>
                    'Nama variant wajib diisi.',

                'variant_name.max' =>
                    'Nama variant maksimal 100 karakter.',

                'color.max' =>
                    'Color maksimal 50 karakter.',

                'material.max' =>
                    'Material maksimal 100 karakter.',

                'price.required' =>
                    'Harga wajib diisi.',

                'price.numeric' =>
                    'Harga harus berupa angka.',

                'price.min' =>
                    'Harga tidak boleh kurang dari 0.',

                'cost_price.numeric' =>
                    'Harga modal harus berupa angka.',

                'cost_price.min' =>
                    'Harga modal tidak boleh kurang dari 0.',

                'stock.required' =>
                    'Stock wajib diisi.',

                'stock.integer' =>
                    'Stock harus berupa bilangan bulat.',

                'stock.min' =>
                    'Stock tidak boleh kurang dari 0.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        $this->variant->update([
            'sku' => $validated['sku'],
            'variant_name' => $validated['variant_name'],
            'color' => $validated['color'] ?: null,
            'material' => $validated['material'] ?: null,
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?: null,
            'stock' => $validated['stock'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Refresh Data
        |--------------------------------------------------------------------------
        */

        $this->variant->refresh();


        /*
        |--------------------------------------------------------------------------
        | Flash Message
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'Product variant berhasil diperbarui.'
        );
    }
};


?>

<div class="space-y-6 p-6">

    {{-- ================================================================
        HEADER
    ================================================================= --}}

    <div>
        <div class="flex items-center justify-between gap-4">

            <div>
                <h1 class="text-xl font-semibold text-gray-900">
                    Edit Product Variant
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Perbarui data variant product.
                </p>
            </div>

            <a
                href="{{ route('products.edit', $product) }}"
                class="rounded-lg border border-gray-300
                       bg-white px-4 py-2.5
                       text-sm font-medium text-gray-700
                       hover:bg-gray-50"
            >
                ← Kembali
            </a>

        </div>
    </div>


    {{-- ================================================================
        FLASH MESSAGE
    ================================================================= --}}

    @if (session()->has('success'))

        <div
            class="flex items-start justify-between gap-4
                   rounded-lg border border-green-200
                   bg-green-50 px-4 py-3
                   text-sm text-green-700"
        >

            <div>
                {{ session('success') }}
            </div>

            <button
                type="button"
                onclick="this.parentElement.remove()"
                class="text-lg font-semibold
                       text-green-600 hover:text-green-800"
            >
                ×
            </button>

        </div>

    @endif


    {{-- ================================================================
        PRODUCT INFORMATION
    ================================================================= --}}

    <div
        class="rounded-xl border border-gray-200
               bg-white p-5"
    >

        <p class="text-xs font-medium uppercase tracking-wide
                  text-gray-500">
            Product
        </p>

        <h2 class="mt-1 text-lg font-semibold text-gray-900">
            {{ $product->name }}
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            SKU Product:
            <span class="font-medium text-gray-700">
                {{ $product->sku }}
            </span>
        </p>

    </div>


    {{-- ================================================================
        FORM EDIT VARIANT
    ================================================================= --}}

    <form
        wire:submit="update"
        class="rounded-xl border border-dashed
               border-gray-300 bg-gray-50 p-5"
    >

        <div class="mb-6">

            <h2 class="text-base font-semibold text-gray-900">
                Edit Variant
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi variant product.
            </p>

        </div>


        {{-- ============================================================
            SKU & NAMA VARIANT
        ============================================================= --}}

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            {{-- SKU --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    SKU Variant
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    wire:model="sku"
                    placeholder="Contoh: PILOT-G2-BLACK-01"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('sku')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- NAMA VARIANT --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Nama Variant
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    wire:model="variant_name"
                    placeholder="Contoh: Black"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('variant_name')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            COLOR & MATERIAL
        ============================================================= --}}

        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

            {{-- COLOR --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Color
                </label>

                <input
                    type="text"
                    wire:model="color"
                    placeholder="Contoh: Black"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('color')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- MATERIAL --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Material
                </label>

                <input
                    type="text"
                    wire:model="material"
                    placeholder="Contoh: Plastic"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('material')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            HARGA, MODAL & STOCK
        ============================================================= --}}

        <div class="mt-5 grid grid-cols-1 gap-5
                    md:grid-cols-3">

            {{-- HARGA --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Harga
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="number"
                    wire:model="price"
                    min="0"
                    step="0.01"
                    placeholder="18000"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('price')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- HARGA MODAL --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Harga Modal
                </label>

                <input
                    type="number"
                    wire:model="cost_price"
                    min="0"
                    step="0.01"
                    placeholder="15000"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('cost_price')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- STOCK --}}

            <div>

                <label
                    class="mb-2 block text-sm font-medium
                           text-gray-700"
                >
                    Stock
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="number"
                    wire:model="stock"
                    min="0"
                    step="1"
                    placeholder="0"
                    class="w-full rounded-lg border
                           border-gray-300 bg-white
                           px-4 py-2.5 text-sm
                           focus:border-blue-500
                           focus:ring-blue-500"
                >

                @error('stock')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            ACTION
        ============================================================= --}}

        <div
            class="mt-6 flex flex-col-reverse gap-3
                   sm:flex-row sm:justify-end"
        >

            <a
                href="{{ route('products.edit', $product) }}"
                class="rounded-lg border border-gray-300
                       bg-white px-5 py-2.5
                       text-center text-sm font-medium
                       text-gray-700 hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="update"
                class="rounded-lg bg-gray-900
                       px-5 py-2.5 text-sm font-medium
                       text-white hover:bg-gray-800
                       disabled:cursor-not-allowed
                       disabled:opacity-50"
            >

                <span
                    wire:loading.remove
                    wire:target="update"
                >
                    Simpan Perubahan
                </span>

                <span
                    wire:loading
                    wire:target="update"
                >
                    Menyimpan...
                </span>

            </button>

        </div>

    </form>

</div>