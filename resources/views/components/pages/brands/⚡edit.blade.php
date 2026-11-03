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

    public Brand $brand;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;


    /*
    |--------------------------------------------------------------------------
    | MOUNT
    |--------------------------------------------------------------------------
    */

    public function mount(Brand $brand): void
    {
        $this->brand = $brand;

        $this->name = $brand->name;

        $this->slug = $brand->slug;

        $this->description = $brand->description ?? '';

        $this->is_active = (bool) $brand->is_active;
    }


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
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name')
                    ->ignore($this->brand->id),
            ],

            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('brands', 'slug')
                    ->ignore($this->brand->id),
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
        | UPDATE DATABASE
        |--------------------------------------------------------------------------
        */

        $this->brand->update($validated);


        /*
        |--------------------------------------------------------------------------
        | FLASH MESSAGE
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'Brand berhasil diperbarui.'
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

    {{-- HEADER --}}
    <div class="mb-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Edit Brand
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi brand.
        </p>

    </div>


    {{-- FORM --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <form wire:submit="update">

            <div class="space-y-5">

                {{-- NAME --}}
                <div>

                    <label
                        for="name"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Nama Brand
                    </label>

                    <input
                        id="name"
                        type="text"
                        wire:model.live="name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"
                    >

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- SLUG --}}
                <div>

                    <label
                        for="slug"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Slug
                    </label>

                    <input
                        id="slug"
                        type="text"
                        wire:model="slug"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"
                    >

                    <p class="mt-1 text-xs text-gray-400">
                        Slug akan mengikuti perubahan nama brand.
                    </p>

                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- DESCRIPTION --}}
                <div>

                    <label
                        for="description"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Deskripsi
                    </label>

                    <textarea
                        id="description"
                        rows="4"
                        wire:model="description"
                        placeholder="Deskripsi singkat tentang brand..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"
                    ></textarea>

                    @error('description')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- STATUS --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Status
                    </label>

                    <label class="inline-flex items-center gap-3">

                        <input
                            type="checkbox"
                            wire:model="is_active"
                            class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500"
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


            {{-- ACTION --}}
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">

                <a
                    href="{{ route('brands.index') }}"
                    wire:navigate
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Kembali
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                >
                    Update Brand
                </button>

            </div>

        </form>

    </div>

</div>