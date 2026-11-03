<?php

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Category;

new class extends Component
{
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public bool $is_active = true;

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:120',
                'unique:categories,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],
        ]);

        Category::create($validated);

    }
};

?>

<div>
    <h1>Tambah Kategori</h1>
    <br />

    <form wire:submit="save"> 
        <input
            type="text"
            wire:model.live="name"
            placeholder="Nama Kategori">
        
        @error('name')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
         @enderror

        <br />
        <input
            type="text"
            wire:model="slug"
            placeholder="Slug"
            readonly>
        <br />
        <textarea
            type="text"
            wire:model="description"
            placeholder="Deskripsi Kategori">
        </textarea>
        <br />
        <input
            type="checkbox"
            wire:model="is_active">

        <button
                    type="submit"
                    class="rounded-lg bg-gray-900
                           px-4 py-2 text-white
                           hover:bg-gray-800"
                >
                    Simpan
       </button>
    </form>

</div>