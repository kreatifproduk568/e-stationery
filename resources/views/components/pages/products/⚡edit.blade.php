<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new  class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Livewire File Upload
    |--------------------------------------------------------------------------
    */

    use WithFileUploads;


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    public Product $product;


    /*
    |--------------------------------------------------------------------------
    | Product Form
    |--------------------------------------------------------------------------
    */

    public string $category_id = '';

    public string $brand_id = '';

    public string $sku = '';

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public bool $is_featured = false;


    /*
    |--------------------------------------------------------------------------
    | Product Image
    |--------------------------------------------------------------------------
    */

    public $image = null;

    public string $image_alt_text = '';


    /*
    |--------------------------------------------------------------------------
    | Product Variant
    |--------------------------------------------------------------------------
    */

    public string $variant_sku = '';

    public string $variant_name = '';

    public string $variant_color = '';

    public string $variant_material = '';

    public string $variant_price = '';

    public string $variant_cost_price = '';

    public string $variant_stock = '0';


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'category',
            'brand',
            'images',
            'variants',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Product Data
        |--------------------------------------------------------------------------
        */

        $this->category_id = (string) $product->category_id;

        $this->brand_id = (string) $product->brand_id;

        $this->sku = $product->sku;

        $this->name = $product->name;

        $this->slug = $product->slug;

        $this->description = $product->description ?? '';

        $this->is_active = (bool) $product->is_active;

        $this->is_featured = (bool) $product->is_featured;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        $validated = $this->validate(
            [
                'category_id' => [
                    'required',
                    'exists:categories,id',
                ],

                'brand_id' => [
                    'required',
                    'exists:brands,id',
                ],

                'sku' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:products,sku,' . $this->product->id,
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
                    'unique:products,slug,' . $this->product->id,
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
            ],
            [
                'category_id.required' =>
                    'Category wajib dipilih.',

                'category_id.exists' =>
                    'Category tidak ditemukan.',

                'brand_id.required' =>
                    'Brand wajib dipilih.',

                'brand_id.exists' =>
                    'Brand tidak ditemukan.',

                'sku.required' =>
                    'SKU product wajib diisi.',

                'sku.unique' =>
                    'SKU product sudah digunakan.',

                'name.required' =>
                    'Nama product wajib diisi.',

                'slug.required' =>
                    'Slug wajib diisi.',

                'slug.unique' =>
                    'Slug sudah digunakan.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        $this->product->update([
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
            'is_featured' => $validated['is_featured'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Refresh Product
        |--------------------------------------------------------------------------
        */

        $this->product->refresh();

        $this->product->load([
            'category',
            'brand',
            'images',
            'variants',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'Product berhasil diperbarui.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Add Product Image
    |--------------------------------------------------------------------------
    */

    public function addImage(): void
    {
        $validated = $this->validate(
            [
                'image' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'image_alt_text' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'image.required' =>
                    'Silakan pilih gambar.',

                'image.image' =>
                    'File harus berupa gambar.',

                'image.mimes' =>
                    'Format harus JPG, JPEG, PNG, atau WEBP.',

                'image.max' =>
                    'Ukuran gambar maksimal 2 MB.',

                'image_alt_text.max' =>
                    'Alt text maksimal 255 karakter.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Store File
        |--------------------------------------------------------------------------
        */

        $imagePath = $this->image->store(
            'products',
            'public'
        );


        /*
        |--------------------------------------------------------------------------
        | Next Sort Order
        |--------------------------------------------------------------------------
        */

        $nextSortOrder = (
            $this->product
                ->images()
                ->max('sort_order') ?? 0
        ) + 1;


        /*
        |--------------------------------------------------------------------------
        | First Image Automatically Primary
        |--------------------------------------------------------------------------
        */

        $hasPrimary = $this->product
            ->images()
            ->where('is_primary', true)
            ->exists();

        $isPrimary = ! $hasPrimary;


        /*
        |--------------------------------------------------------------------------
        | Create Image Record
        |--------------------------------------------------------------------------
        */

        $this->product->images()->create([
            'image_path' => $imagePath,

            'alt_text' => $validated['image_alt_text']
                ?: $this->product->name,

            'sort_order' => $nextSortOrder,

            'is_primary' => $isPrimary,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $this->product->load('images');


        /*
        |--------------------------------------------------------------------------
        | Reset Upload Form
        |--------------------------------------------------------------------------
        */

        $this->reset([
            'image',
            'image_alt_text',
        ]);


        session()->flash(
            'image_success',
            'Gambar product berhasil ditambahkan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Product Image
    |--------------------------------------------------------------------------
    */

    public function deleteImage(string $imageId): void
    {
        $image = $this->product
            ->images()
            ->whereKey($imageId)
            ->firstOrFail();

        $wasPrimary = (bool) $image->is_primary;

        $imagePath = $image->image_path;

        $image->delete();


        /*
        |--------------------------------------------------------------------------
        | Delete Physical File
        |--------------------------------------------------------------------------
        */

        if (
            $imagePath &&
            Storage::disk('public')->exists($imagePath)
        ) {
            Storage::disk('public')->delete($imagePath);
        }


        /*
        |--------------------------------------------------------------------------
        | If Primary Was Deleted
        | Set First Remaining Image As Primary
        |--------------------------------------------------------------------------
        */

        if ($wasPrimary) {
            $newPrimary = $this->product
                ->images()
                ->orderBy('sort_order')
                ->first();

            if ($newPrimary) {
                $newPrimary->update([
                    'is_primary' => true,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $this->product->load('images');


        session()->flash(
            'image_success',
            'Gambar product berhasil dihapus.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Set Primary Image
    |--------------------------------------------------------------------------
    */

    public function setPrimaryImage(string $imageId): void
    {
        $image = $this->product
            ->images()
            ->whereKey($imageId)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Remove Existing Primary
        |--------------------------------------------------------------------------
        */

        $this->product
            ->images()
            ->where('is_primary', true)
            ->update([
                'is_primary' => false,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Set Selected Image
        |--------------------------------------------------------------------------
        */

        $image->update([
            'is_primary' => true,
        ]);


        $this->product->load('images');


        session()->flash(
            'image_success',
            'Gambar utama product berhasil diubah.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Move Image Up
    |--------------------------------------------------------------------------
    */

    public function moveImageUp(string $imageId): void
    {
        $image = $this->product
            ->images()
            ->whereKey($imageId)
            ->firstOrFail();

        $previousImage = $this->product
            ->images()
            ->where(
                'sort_order',
                '<',
                $image->sort_order
            )
            ->orderByDesc('sort_order')
            ->first();


        if (! $previousImage) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Swap Sort Order
        |--------------------------------------------------------------------------
        */

        $currentOrder = $image->sort_order;

        $image->update([
            'sort_order' => $previousImage->sort_order,
        ]);

        $previousImage->update([
            'sort_order' => $currentOrder,
        ]);


        $this->product->load('images');
    }


    /*
    |--------------------------------------------------------------------------
    | Move Image Down
    |--------------------------------------------------------------------------
    */

    public function moveImageDown(string $imageId): void
    {
        $image = $this->product
            ->images()
            ->whereKey($imageId)
            ->firstOrFail();

        $nextImage = $this->product
            ->images()
            ->where(
                'sort_order',
                '>',
                $image->sort_order
            )
            ->orderBy('sort_order')
            ->first();


        if (! $nextImage) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Swap Sort Order
        |--------------------------------------------------------------------------
        */

        $currentOrder = $image->sort_order;

        $image->update([
            'sort_order' => $nextImage->sort_order,
        ]);

        $nextImage->update([
            'sort_order' => $currentOrder,
        ]);


        $this->product->load('images');
    }


    /*
    |--------------------------------------------------------------------------
    | Add Product Variant
    |--------------------------------------------------------------------------
    */

    public function addVariant(): void
    {
        $validated = $this->validate(
            [
                'variant_sku' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:product_variants,sku',
                ],

                'variant_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'variant_color' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'variant_material' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'variant_price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'variant_cost_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'variant_stock' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'variant_sku.required' =>
                    'SKU variant wajib diisi.',

                'variant_sku.unique' =>
                    'SKU variant sudah digunakan.',

                'variant_name.required' =>
                    'Nama variant wajib diisi.',

                'variant_price.required' =>
                    'Harga variant wajib diisi.',

                'variant_price.numeric' =>
                    'Harga variant harus berupa angka.',

                'variant_stock.required' =>
                    'Stock wajib diisi.',

                'variant_stock.integer' =>
                    'Stock harus berupa bilangan bulat.',

                'variant_stock.min' =>
                    'Stock tidak boleh kurang dari 0.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Create Variant
        |--------------------------------------------------------------------------
        */

        $this->product->variants()->create([
            'sku' => $validated['variant_sku'],

            'variant_name' => $validated['variant_name'],

            'color' => $validated['variant_color'] ?: null,

            'material' => $validated['variant_material'] ?: null,

            'price' => $validated['variant_price'],

            'cost_price' =>
                $validated['variant_cost_price'] ?: null,

            'stock' => $validated['variant_stock'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Refresh Variants
        |--------------------------------------------------------------------------
        */

        $this->product->load('variants');


        /*
        |--------------------------------------------------------------------------
        | Reset Variant Form
        |--------------------------------------------------------------------------
        */

        $this->reset([
            'variant_sku',
            'variant_name',
            'variant_color',
            'variant_material',
            'variant_price',
            'variant_cost_price',
        ]);

        $this->variant_stock = '0';


        session()->flash(
            'variant_success',
            'Product variant berhasil ditambahkan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Product Variant
    |--------------------------------------------------------------------------
    */

    public function deleteVariant(string $variantId): void
    {
        $variant = $this->product
            ->variants()
            ->whereKey($variantId)
            ->firstOrFail();

        $sku = $variant->sku;


        /*
        |--------------------------------------------------------------------------
        | Delete Variant
        |--------------------------------------------------------------------------
        */

        $variant->delete();


        /*
        |--------------------------------------------------------------------------
        | Reload Variants
        |--------------------------------------------------------------------------
        */

        $this->product->load('variants');


        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'variant_success',
            "Variant {$sku} berhasil dihapus."
        );
    }
};
?>

<div
    x-data="{
        showDeleteImageModal: false,
        deleteImageId: null,
        deleteImageName: '',

        showDeleteVariantModal: false,
        deleteVariantId: null,
        deleteVariantSku: '',

        openDeleteImageModal(id, name) {
            this.deleteImageId = id
            this.deleteImageName = name
            this.showDeleteImageModal = true
        },

        closeDeleteImageModal() {
            this.showDeleteImageModal = false
            this.deleteImageId = null
            this.deleteImageName = ''
        },

        confirmDeleteImage() {
            if (!this.deleteImageId) {
                return
            }

            $wire.deleteImage(this.deleteImageId)

            this.closeDeleteImageModal()
        },

        openDeleteVariantModal(id, sku) {
            this.deleteVariantId = id
            this.deleteVariantSku = sku
            this.showDeleteVariantModal = true
        },

        closeDeleteVariantModal() {
            this.showDeleteVariantModal = false
            this.deleteVariantId = null
            this.deleteVariantSku = ''
        },

        confirmDeleteVariant() {
            if (!this.deleteVariantId) {
                return
            }

            $wire.deleteVariant(this.deleteVariantId)

            this.closeDeleteVariantModal()
        }
    }"
    class="space-y-6 p-6"
>

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Product
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi product, gambar, dan variant.
            </p>
        </div>

        <a
            href="{{ route('products.index') }}"
            class="rounded-lg border border-gray-300
                   bg-white px-4 py-2.5
                   text-sm font-medium text-gray-700
                   hover:bg-gray-50"
        >
            ← Kembali
        </a>

    </div>


    {{-- ================================================================
        FLASH MESSAGE - PRODUCT
    ================================================================= --}}

    @if (session('success'))

        <div
            x-data="{ show: true }"
            x-show="show"
            class="flex items-center justify-between
                   rounded-lg border border-green-200
                   bg-green-50 px-4 py-3
                   text-sm text-green-700"
        >

            <span>
                {{ session('success') }}
            </span>

            <button
                type="button"
                @click="show = false"
                class="ml-4 text-lg font-semibold
                       text-green-700 hover:text-green-900"
            >
                ×
            </button>

        </div>

    @endif


    {{-- ================================================================
        PRODUCT INFORMATION
    ================================================================= --}}

    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <div class="mb-6">

            <h2 class="text-base font-semibold text-gray-900">
                Informasi Product
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi dasar product.
            </p>

        </div>


        {{-- PRODUCT FORM --}}
        <form wire:submit="update">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- Category --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Category
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model="category_id"
                        class="w-full rounded-lg border border-gray-300
                               bg-white px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                        <option value="">
                            -- Pilih Category --
                        </option>

                        @foreach (Category::orderBy('name')->get() as $category)

                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Brand --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Brand
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        wire:model="brand_id"
                        class="w-full rounded-lg border border-gray-300
                               bg-white px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                        <option value="">
                            -- Pilih Brand --
                        </option>

                        @foreach (Brand::orderBy('name')->get() as $brand)

                            <option value="{{ $brand->id }}">
                                {{ $brand->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('brand_id')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- SKU --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        SKU Product
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="sku"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('sku')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Name --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Nama Product
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="name"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Slug --}}
                <div class="md:col-span-2">

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Slug
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="slug"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Description --}}
                <div class="md:col-span-2">

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Description
                    </label>

                    <textarea
                        wire:model="description"
                        rows="4"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm
                               focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>

                    @error('description')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- STATUS --}}
            <div
                class="mt-6 grid grid-cols-1 gap-4
                       md:grid-cols-2"
            >

                <label
                    class="flex cursor-pointer items-center gap-3
                           rounded-lg border border-gray-200
                           p-4"
                >

                    <input
                        type="checkbox"
                        wire:model="is_active"
                        class="h-4 w-4 rounded border-gray-300"
                    >

                    <div>

                        <p class="text-sm font-medium text-gray-900">
                            Product Aktif
                        </p>

                        <p class="text-xs text-gray-500">
                            Product dapat digunakan dan ditampilkan.
                        </p>

                    </div>

                </label>


                <label
                    class="flex cursor-pointer items-center gap-3
                           rounded-lg border border-gray-200
                           p-4"
                >

                    <input
                        type="checkbox"
                        wire:model="is_featured"
                        class="h-4 w-4 rounded border-gray-300"
                    >

                    <div>

                        <p class="text-sm font-medium text-gray-900">
                            Product Featured
                        </p>

                        <p class="text-xs text-gray-500">
                            Tandai sebagai product unggulan.
                        </p>

                    </div>

                </label>

            </div>


            {{-- UPDATE BUTTON --}}
            <div class="mt-6 flex justify-end">

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


    {{-- ================================================================
        PRODUCT IMAGES
    ================================================================= --}}

    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <div class="mb-6">

            <h2 class="text-base font-semibold text-gray-900">
                Product Images
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola gambar product, gambar utama, dan urutan gambar.
            </p>

        </div>


        {{-- FLASH IMAGE --}}
        @if (session('image_success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                class="mb-5 flex items-center justify-between
                       rounded-lg border border-green-200
                       bg-green-50 px-4 py-3
                       text-sm text-green-700"
            >

                <span>
                    {{ session('image_success') }}
                </span>

                <button
                    type="button"
                    @click="show = false"
                    class="ml-4 text-lg font-semibold"
                >
                    ×
                </button>

            </div>

        @endif


        {{-- ADD IMAGE --}}
        <div
            class="mb-6 rounded-xl border border-dashed
                   border-gray-300 bg-gray-50 p-5"
        >

            <div class="mb-4">

                <h3 class="text-sm font-semibold text-gray-900">
                    Tambah Gambar
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- IMAGE --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Gambar
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="file"
                        wire:model="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full rounded-lg border
                               border-gray-300 bg-white
                               text-sm text-gray-700
                               file:mr-4 file:border-0
                               file:bg-gray-100 file:px-4
                               file:py-2 file:text-sm
                               file:font-medium"
                    >

                    @error('image')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <div
                        wire:loading
                        wire:target="image"
                        class="mt-2 text-xs text-gray-500"
                    >
                        Memproses gambar...
                    </div>

                </div>


                {{-- ALT --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Alt Text
                    </label>

                    <input
                        type="text"
                        wire:model="image_alt_text"
                        placeholder="Contoh: Pulpen Pilot G2 warna hitam"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('image_alt_text')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- PREVIEW --}}
            @if ($image)

                <div class="mt-5">

                    <p class="mb-2 text-sm font-medium text-gray-700">
                        Preview
                    </p>

                    <div
                        class="h-40 w-40 overflow-hidden
                               rounded-lg border bg-white"
                    >

                        <img
                            src="{{ $image->temporaryUrl() }}"
                            alt="Preview"
                            class="h-full w-full object-contain"
                        >

                    </div>

                </div>

            @endif


            <div class="mt-5">

                <button
                    type="button"
                    wire:click="addImage"
                    wire:loading.attr="disabled"
                    wire:target="addImage"
                    class="rounded-lg bg-gray-900
                           px-4 py-2.5 text-sm font-medium
                           text-white hover:bg-gray-800
                           disabled:cursor-not-allowed
                           disabled:opacity-50"
                >

                    <span
                        wire:loading.remove
                        wire:target="addImage"
                    >
                        + Tambah Gambar
                    </span>

                    <span
                        wire:loading
                        wire:target="addImage"
                    >
                        Menyimpan...
                    </span>

                </button>

            </div>

        </div>


        {{-- IMAGE GRID --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            @forelse ($product->images as $image)

                <div
                    wire:key="product-image-{{ $image->id }}"
                    class="overflow-hidden rounded-xl
                           border border-gray-200 bg-white"
                >

                    {{-- IMAGE --}}
                    <div class="aspect-square bg-gray-100">

                        <img
                            src="{{ asset('storage/' . $image->image_path) }}"
                            alt="{{ $image->alt_text ?? $product->name }}"
                            class="h-full w-full object-contain"
                        >

                    </div>


                    {{-- INFORMATION --}}
                    <div class="space-y-4 p-4">

                        <div
                            class="flex items-center justify-between"
                        >

                            <span class="text-sm text-gray-500">
                                Urutan {{ $image->sort_order }}
                            </span>


                            @if ($image->is_primary)

                                <span
                                    class="rounded-full bg-blue-100
                                           px-4 py-2 text-sm
                                           font-medium text-blue-700"
                                >
                                    Primary
                                </span>

                            @endif

                        </div>


                        <p class="text-base text-gray-700">
                            {{ $image->alt_text ?? $product->name }}
                        </p>


                        {{-- SORT BUTTONS --}}
                        <div class="grid grid-cols-2 gap-3">

                            {{-- UP --}}
                            <button
                                type="button"
                                wire:click="moveImageUp('{{ $image->id }}')"
                                @disabled($loop->first)
                                class="rounded-lg border border-gray-300
                                       px-4 py-3 text-sm font-medium
                                       text-gray-700
                                       hover:bg-gray-50
                                       disabled:cursor-not-allowed
                                       disabled:bg-gray-100
                                       disabled:text-gray-400"
                            >
                                ↑ Naik
                            </button>


                            {{-- DOWN --}}
                            <button
                                type="button"
                                wire:click="moveImageDown('{{ $image->id }}')"
                                @disabled($loop->last)
                                class="rounded-lg border border-gray-300
                                       px-4 py-3 text-sm font-medium
                                       text-gray-700
                                       hover:bg-gray-50
                                       disabled:cursor-not-allowed
                                       disabled:bg-gray-100
                                       disabled:text-gray-400"
                            >
                                ↓ Turun
                            </button>

                        </div>


                        {{-- PRIMARY --}}
                        @if ($image->is_primary)

                            <div
                                class="rounded-lg bg-blue-50
                                       px-4 py-3 text-center
                                       text-sm font-semibold
                                       text-blue-700"
                            >
                                Gambar Utama Product
                            </div>

                        @else

                            <button
                                type="button"
                                wire:click="setPrimaryImage('{{ $image->id }}')"
                                class="w-full rounded-lg
                                       border border-gray-300
                                       px-4 py-3 text-sm
                                       font-medium text-gray-700
                                       hover:bg-gray-50"
                            >
                                Jadikan Primary
                            </button>

                        @endif


                        {{-- DELETE --}}
                        <button
                            type="button"
                            @click="openDeleteImageModal(
                                '{{ $image->id }}',
                                '{{ addslashes($image->alt_text ?? $product->name) }}'
                            )"
                            class="w-full rounded-lg
                                   border border-red-200
                                   bg-red-50 px-4 py-3
                                   text-sm font-medium
                                   text-red-600
                                   hover:bg-red-100"
                        >
                            Hapus Gambar
                        </button>

                    </div>

                </div>

            @empty

                <div
                    class="col-span-full rounded-xl
                           border border-dashed
                           border-gray-300 bg-gray-50
                           p-10 text-center"
                >
                    <p class="text-sm text-gray-500">
                        Belum ada gambar product.
                    </p>
                </div>

            @endforelse

        </div>

    </div>


    {{-- ================================================================
        PRODUCT VARIANTS
    ================================================================= --}}

    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <div class="mb-6">

            <h2 class="text-base font-semibold text-gray-900">
                Product Variants
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola variasi product, harga, dan stock.
            </p>

        </div>


        {{-- FLASH VARIANT --}}
        @if (session('variant_success'))

            <div
                x-data="{ show: true }"
                x-show="show"
                class="mb-5 flex items-center justify-between
                       rounded-lg border border-green-200
                       bg-green-50 px-4 py-3
                       text-sm text-green-700"
            >

                <span>
                    {{ session('variant_success') }}
                </span>

                <button
                    type="button"
                    @click="show = false"
                    class="ml-4 text-lg font-semibold"
                >
                    ×
                </button>

            </div>

        @endif


        {{-- ADD VARIANT --}}
        <div
            class="mb-8 rounded-xl border border-dashed
                   border-gray-300 bg-gray-50 p-5"
        >

            <div class="mb-5">

                <h3 class="text-sm font-semibold text-gray-900">
                    Tambah Variant
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Tambahkan variasi baru untuk product ini.
                </p>

            </div>


            {{-- SKU + NAME --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- SKU --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        SKU Variant
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="variant_sku"
                        placeholder="Contoh: PILOT-G2-BLACK-01"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_sku')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- NAME --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Nama Variant
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="variant_name"
                        placeholder="Contoh: Black"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_name')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- COLOR --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Color
                    </label>

                    <input
                        type="text"
                        wire:model="variant_color"
                        placeholder="Contoh: Black"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_color')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- MATERIAL --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Material
                    </label>

                    <input
                        type="text"
                        wire:model="variant_material"
                        placeholder="Contoh: Plastic"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_material')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- PRICE + COST + STOCK --}}
            <div
                class="mt-5 grid grid-cols-1 gap-5
                       md:grid-cols-3"
            >

                {{-- PRICE --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Harga
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        wire:model="variant_price"
                        min="0"
                        step="0.01"
                        placeholder="18000"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_price')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- COST PRICE --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Harga Modal
                    </label>

                    <input
                        type="number"
                        wire:model="variant_cost_price"
                        min="0"
                        step="0.01"
                        placeholder="15000"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_cost_price')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- STOCK --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Stock
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        wire:model="variant_stock"
                        min="0"
                        step="1"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-2.5 text-sm"
                    >

                    @error('variant_stock')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- ADD VARIANT BUTTON --}}
            <div class="mt-5">

                <button
                    type="button"
                    wire:click="addVariant"
                    wire:loading.attr="disabled"
                    wire:target="addVariant"
                    class="rounded-lg bg-gray-900
                           px-4 py-2.5
                           text-sm font-medium text-white
                           hover:bg-gray-800
                           disabled:cursor-not-allowed
                           disabled:opacity-50"
                >

                    <span
                        wire:loading.remove
                        wire:target="addVariant"
                    >
                        + Tambah Variant
                    </span>

                    <span
                        wire:loading
                        wire:target="addVariant"
                    >
                        Menyimpan...
                    </span>

                </button>

            </div>

        </div>


        {{-- ============================================================
            VARIANT TABLE
        ============================================================= --}}

        <div class="overflow-x-auto rounded-xl border border-gray-200">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th
                            class="px-4 py-3 text-left
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            SKU
                        </th>

                        <th
                            class="px-4 py-3 text-left
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Variant
                        </th>

                        <th
                            class="px-4 py-3 text-left
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Color
                        </th>

                        <th
                            class="px-4 py-3 text-left
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Material
                        </th>

                        <th
                            class="px-4 py-3 text-right
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Harga
                        </th>

                        <th
                            class="px-4 py-3 text-right
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Modal
                        </th>

                        <th
                            class="px-4 py-3 text-right
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Stock
                        </th>

                        <th
                            class="px-4 py-3 text-right
                                   text-xs font-medium uppercase
                                   tracking-wider text-gray-500"
                        >
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($product->variants as $variant)

                        <tr
                            wire:key="product-variant-{{ $variant->id }}"
                            class="hover:bg-gray-50"
                        >

                            {{-- SKU --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                <span
                                    class="text-sm font-semibold
                                           text-gray-900"
                                >
                                    {{ $variant->sku }}
                                </span>

                            </td>


                            {{-- VARIANT --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                <span class="text-sm text-gray-700">
                                    {{ $variant->variant_name }}
                                </span>

                            </td>


                            {{-- COLOR --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                <span class="text-sm text-gray-700">
                                    {{ $variant->color ?? '-' }}
                                </span>

                            </td>


                            {{-- MATERIAL --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                <span class="text-sm text-gray-700">
                                    {{ $variant->material ?? '-' }}
                                </span>

                            </td>


                            {{-- PRICE --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right">

                                <span class="text-sm text-gray-700">
                                    Rp
                                    {{ number_format($variant->price, 0, ',', '.') }}
                                </span>

                            </td>


                            {{-- COST --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right">

                                <span class="text-sm text-gray-700">
                                    Rp
                                    {{ number_format($variant->cost_price, 0, ',', '.') }}
                                </span>

                            </td>


                            {{-- STOCK --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right">

                                <span
                                    class="text-sm font-semibold
                                           text-gray-900"
                                >
                                    {{ $variant->stock }}
                                </span>

                            </td>


                            {{-- ACTION --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                <div class="flex justify-end gap-2">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route(
                                            'products.variants.edit',
                                            [
                                                'product' => $product->id,
                                                'variant' => $variant->id,
                                            ]
                                        ) }}"
                                        class="rounded-lg border
                                               border-gray-300
                                               bg-white px-4 py-2
                                               text-sm font-medium
                                               text-gray-700
                                               hover:bg-gray-50"
                                    >
                                        Edit
                                    </a>


                                    {{-- DELETE --}}
                                    <button
                                        type="button"
                                        @click="openDeleteVariantModal(
                                            '{{ $variant->id }}',
                                            '{{ addslashes($variant->sku) }}'
                                        )"
                                        class="rounded-lg border
                                               border-red-200
                                               bg-red-50 px-4 py-2
                                               text-sm font-medium
                                               text-red-600
                                               hover:bg-red-100"
                                    >
                                        Hapus
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="px-4 py-10 text-center"
                            >

                                <p class="text-sm text-gray-500">
                                    Belum ada product variant.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ================================================================
        MODAL DELETE IMAGE
    ================================================================= --}}

    <div
        x-show="showDeleteImageModal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center
               justify-center bg-black/50 px-4"
        @keydown.escape.window="closeDeleteImageModal()"
    >

        <div
            x-show="showDeleteImageModal"
            x-transition
            @click.outside="closeDeleteImageModal()"
            class="w-full max-w-md overflow-hidden
                   rounded-2xl bg-white shadow-2xl"
        >

            {{-- HEADER --}}
            <div class="border-b border-gray-200 px-6 py-5">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center
                               justify-center rounded-full
                               bg-red-100"
                    >

                        <svg
                            class="h-5 w-5 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01
                                   M10.29 3.86l-7.5 13A2 2 0
                                   004.5 20h15a2 2 0 001.71-3.14
                                   l-7.5-13a2 2 0 00-3.42 0z"
                            />
                        </svg>

                    </div>

                    <div>

                        <h3 class="text-base font-semibold text-gray-900">
                            Hapus Gambar
                        </h3>

                        <p class="text-sm text-gray-500">
                            Konfirmasi penghapusan
                        </p>

                    </div>

                </div>

            </div>


            {{-- BODY --}}
            <div class="px-6 py-5">

                <p class="text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus gambar:
                </p>

                <div
                    class="mt-3 rounded-lg bg-gray-50
                           px-4 py-3 text-sm font-semibold
                           text-gray-900"
                    x-text="deleteImageName"
                ></div>

                <p class="mt-3 text-sm text-red-600">
                    File gambar juga akan dihapus dari storage.
                </p>

            </div>


            {{-- FOOTER --}}
            <div
                class="flex justify-end gap-3
                       border-t border-gray-200
                       px-6 py-4"
            >

                <button
                    type="button"
                    @click="closeDeleteImageModal()"
                    class="rounded-lg border border-gray-300
                           bg-white px-4 py-2.5
                           text-sm font-medium text-gray-700
                           hover:bg-gray-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="confirmDeleteImage()"
                    wire:loading.attr="disabled"
                    wire:target="deleteImage"
                    class="rounded-lg bg-red-600
                           px-4 py-2.5 text-sm font-medium
                           text-white hover:bg-red-700
                           disabled:cursor-not-allowed
                           disabled:opacity-50"
                >

                    <span
                        wire:loading.remove
                        wire:target="deleteImage"
                    >
                        Hapus Gambar
                    </span>

                    <span
                        wire:loading
                        wire:target="deleteImage"
                    >
                        Menghapus...
                    </span>

                </button>

            </div>

        </div>

    </div>


    {{-- ================================================================
        MODAL DELETE VARIANT
    ================================================================= --}}

    <div
        x-show="showDeleteVariantModal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center
               justify-center bg-black/50 px-4"
        @keydown.escape.window="closeDeleteVariantModal()"
    >

        <div
            x-show="showDeleteVariantModal"
            x-transition
            @click.outside="closeDeleteVariantModal()"
            class="w-full max-w-md overflow-hidden
                   rounded-2xl bg-white shadow-2xl"
        >

            {{-- HEADER --}}
            <div class="border-b border-gray-200 px-6 py-5">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center
                               justify-center rounded-full
                               bg-red-100"
                    >

                        <svg
                            class="h-5 w-5 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01
                                   M10.29 3.86l-7.5 13A2 2 0
                                   004.5 20h15a2 2 0 001.71-3.14
                                   l-7.5-13a2 2 0 00-3.42 0z"
                            />
                        </svg>

                    </div>

                    <div>

                        <h3 class="text-base font-semibold text-gray-900">
                            Hapus Product Variant
                        </h3>

                        <p class="text-sm text-gray-500">
                            Konfirmasi penghapusan data
                        </p>

                    </div>

                </div>

            </div>


            {{-- BODY --}}
            <div class="px-6 py-5">

                <p class="text-sm leading-6 text-gray-600">
                    Apakah Anda yakin ingin menghapus variant:
                </p>

                <div
                    class="mt-3 rounded-lg bg-gray-50
                           px-4 py-3 font-mono
                           text-sm font-semibold text-gray-900"
                    x-text="deleteVariantSku"
                ></div>

                <p class="mt-3 text-sm text-red-600">
                    Data variant yang sudah dihapus
                    tidak dapat dikembalikan.
                </p>

            </div>


            {{-- FOOTER --}}
            <div
                class="flex justify-end gap-3
                       border-t border-gray-200
                       px-6 py-4"
            >

                {{-- CANCEL --}}
                <button
                    type="button"
                    @click="closeDeleteVariantModal()"
                    class="rounded-lg border border-gray-300
                           bg-white px-4 py-2.5
                           text-sm font-medium text-gray-700
                           hover:bg-gray-50"
                >
                    Batal
                </button>


                {{-- CONFIRM --}}
                <button
                    type="button"
                    @click="confirmDeleteVariant()"
                    wire:loading.attr="disabled"
                    wire:target="deleteVariant"
                    class="rounded-lg bg-red-600
                           px-4 py-2.5
                           text-sm font-medium text-white
                           hover:bg-red-700
                           disabled:cursor-not-allowed
                           disabled:opacity-50"
                >

                    <span
                        wire:loading.remove
                        wire:target="deleteVariant"
                    >
                        Hapus Variant
                    </span>

                    <span
                        wire:loading
                        wire:target="deleteVariant"
                    >
                        Menghapus...
                    </span>

                </button>

            </div>

        </div>

    </div>


    {{-- ================================================================
        ALPINE CLOAK
    ================================================================= --}}

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</div>