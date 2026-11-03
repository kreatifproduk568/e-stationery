<?php

use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public bool $is_active = true;


    public function save(){
        echo $this->name;
        echo $this->slug;
        echo $this->description;
        echo $this->is_active;

    }

};
?>

<div>
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6">

        <form wire:submit="save">

            {{-- NAME --}}
            <div class="mb-5">

                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Nama Category
                </label>

                <input
                    id="name"
                    type="text"
                    wire:model.live="name"
                    placeholder="Contoh: Alat Tulis"
                    class="w-full rounded-lg border border-gray-300
                           px-4 py-2
                           focus:border-gray-500
                           focus:ring-1 focus:ring-gray-500"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- SLUG --}}
            <div class="mb-5">

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
                    readonly
                    class="w-full rounded-lg border border-gray-300
                           bg-gray-50 px-4 py-2 text-gray-600"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Slug dibuat otomatis dari nama category.
                </p>

                @error('slug')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- DESCRIPTION --}}
            <div class="mb-5">

                <label
                    for="description"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Description
                </label>

                <textarea
                    id="description"
                    wire:model="description"
                    rows="4"
                    placeholder="Deskripsi category..."
                    class="w-full rounded-lg border border-gray-300
                           px-4 py-2
                           focus:border-gray-500
                           focus:ring-1 focus:ring-gray-500"
                ></textarea>

                @error('description')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- ACTIVE STATUS --}}
            <div class="mb-6">

                <label class="inline-flex items-center gap-3">

                    <input
                        type="checkbox"
                        wire:model="is_active"
                        class="h-4 w-4 rounded border-gray-300"
                    >

                    <span class="text-sm font-medium text-gray-700">
                        Category Aktif
                    </span>

                </label>

                @error('is_active')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- ACTION BUTTONS --}}
            <div class="flex justify-end gap-2">

                <a
                    href="{{ route('categories.index') }}"
                    class="rounded-lg border border-gray-300
                           px-4 py-2 text-gray-700
                           hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-gray-900
                           px-4 py-2 text-white
                           hover:bg-gray-800"
                >
                    Simpan
                </button>

            </div>

        </form>

    </div>
</div>