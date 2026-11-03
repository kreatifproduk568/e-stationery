<?php

use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;


    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */

    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name'),
            ],

            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('brands', 'slug'),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        Brand::create($validated);


        /*
        |--------------------------------------------------------------------------
        | FLASH MESSAGE
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'Brand berhasil ditambahkan.'
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        $this->redirectRoute(
            'brands.index',
            navigate: true
        );
    }
};
?>

<div class="p-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="mb-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Tambah Brand
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Tambahkan brand baru ke dalam sistem.
        </p>

    </div>


    {{-- =========================================================
         FORM
    ========================================================== --}}

    <div
        class="rounded-xl
               border border-gray-200
               bg-white
               p-6"
    >

        <form wire:submit="save">

            <div class="space-y-5">

                {{-- =================================================
                     NAMA BRAND
                ================================================== --}}

                <div>

                    <label
                        for="name"
                        class="mb-2 block
                               text-sm
                               font-medium
                               text-gray-700"
                    >
                        Nama Brand
                    </label>

                    <input
                        id="name"
                        type="text"
                        wire:model.live="name"
                        placeholder="Contoh: Faber-Castell"
                        class="w-full
                               rounded-lg
                               border border-gray-300
                               px-4 py-2.5
                               text-sm
                               focus:border-gray-500
                               focus:outline-none
                               focus:ring-1
                               focus:ring-gray-500"
                    >

                    @error('name')

                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- =================================================
                     SLUG
                ================================================== --}}

                <div>

                    <label
                        for="slug"
                        class="mb-2 block
                               text-sm
                               font-medium
                               text-gray-700"
                    >
                        Slug
                    </label>

                    <input
                        id="slug"
                        type="text"
                        wire:model="slug"
                        placeholder="faber-castell"
                        class="w-full
                               rounded-lg
                               border border-gray-300
                               bg-gray-50
                               px-4 py-2.5
                               text-sm
                               focus:border-gray-500
                               focus:outline-none
                               focus:ring-1
                               focus:ring-gray-500"
                    >

                    <p class="mt-1 text-xs text-gray-400">
                        Slug dibuat otomatis berdasarkan nama brand.
                    </p>

                    @error('slug')

                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- =================================================
                     DESCRIPTION
                ================================================== --}}

                <div>

                    <label
                        for="description"
                        class="mb-2 block
                               text-sm
                               font-medium
                               text-gray-700"
                    >
                        Deskripsi
                    </label>

                    <textarea
                        id="description"
                        rows="4"
                        wire:model="description"
                        placeholder="Deskripsi singkat tentang brand..."
                        class="w-full
                               rounded-lg
                               border border-gray-300
                               px-4 py-2.5
                               text-sm
                               focus:border-gray-500
                               focus:outline-none
                               focus:ring-1
                               focus:ring-gray-500"
                    ></textarea>

                    @error('description')

                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- =================================================
                     STATUS
                ================================================== --}}

                <div>

                    <label
                        class="mb-2 block
                               text-sm
                               font-medium
                               text-gray-700"
                    >
                        Status
                    </label>

                    <label
                        class="inline-flex
                               items-center
                               gap-3"
                    >

                        <input
                            type="checkbox"
                            wire:model="is_active"
                            class="h-4 w-4
                                   rounded
                                   border-gray-300
                                   text-gray-900
                                   focus:ring-gray-500"
                        >

                        <span class="text-sm text-gray-700">
                            Brand aktif
                        </span>

                    </label>

                    @error('is_active')

                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </div>


            {{-- =================================================
                 BUTTON
            ================================================== --}}

            <div
                class="mt-6
                       flex
                       items-center
                       justify-end
                       gap-3
                       border-t
                       border-gray-200
                       pt-5"
            >

                {{-- BATAL --}}

                <a
                    href="{{ route('brands.index') }}"
                    class="rounded-lg
                           border border-gray-300
                           px-4 py-2
                           text-sm
                           font-medium
                           text-gray-700
                           hover:bg-gray-50"
                >
                    Batal
                </a>


                {{-- SIMPAN --}}

                <button
                    type="submit"
                    class="rounded-lg
                           bg-gray-900
                           px-4 py-2
                           text-sm
                           font-medium
                           text-white
                           hover:bg-gray-800"
                >
                    Simpan Brand
                </button>

            </div>

        </form>

    </div>

</div>