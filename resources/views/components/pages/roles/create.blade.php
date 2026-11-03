<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public string $name = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->can('roles.create'), 403);
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can('roles.create'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web'),
            ],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        Role::create([
            'name' => trim($validated['name']),
            'guard_name' => 'web',
        ]);

        session()->flash('success', 'Role berhasil dibuat.');

        $this->redirectRoute('roles.index');
    }
};
?>

<div class="mx-auto max-w-2xl p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            Tambah Role
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Buat role baru untuk mengatur hak akses pengguna.
        </p>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6">
        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                Nama Role
            </label>

            <input
                id="name"
                type="text"
                wire:model="name"
                placeholder="Contoh: Staff Pembelian"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />

            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-sm text-gray-500">
            Role baru belum memiliki permission. Hak akses dapat diatur
            melalui halaman detail role setelah role dibuat.
        </p>

        <div class="flex justify-end gap-3">
            <a
                href="{{ route('roles.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="save">
                    Simpan Role
                </span>

                <span wire:loading wire:target="save">
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
