<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public Role $role;

    public string $name = '';

    public function mount(Role $role): void
    {
        abort_unless(Auth::user()->can('roles.update'), 403);
        abort_unless($role->guard_name === 'web', 404);

        // Super Admin tidak boleh diubah namanya.
        abort_if($role->name === 'Super Admin', 403);

        $this->role = $role;
        $this->name = $role->name;
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can('roles.update'), 403);

        // Periksa kembali agar role Super Admin tetap terlindungi.
        $this->role->refresh();

        abort_if($this->role->name === 'Super Admin', 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->role->id),
            ],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        $this->role->update([
            'name' => trim($validated['name']),
        ]);

        session()->flash('success', 'Nama role berhasil diperbarui.');

        $this->redirectRoute('roles.show', [
            'role' => $this->role->id,
        ]);
    }
};
?>

<div class="mx-auto max-w-2xl p-6">
    <div class="mb-6">
        <a
            href="{{ route('roles.show', $role->id) }}"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
        >
            &larr; Kembali ke Detail Role
        </a>

        <h1 class="mt-3 text-2xl font-bold text-gray-900">
            Edit Role
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Ubah nama role tanpa mengubah permission yang sudah diberikan.
        </p>
    </div>

    <form
        wire:submit="save"
        class="space-y-6 rounded-xl border border-gray-200 bg-white p-6"
    >
        <div>
            <label
                for="name"
                class="mb-2 block text-sm font-medium text-gray-700"
            >
                Nama Role
            </label>

            <input
                id="name"
                type="text"
                wire:model="name"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >

            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3">
            <a
                href="{{ route('roles.show', $role->id) }}"
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
                    Simpan Perubahan
                </span>

                <span wire:loading wire:target="save">
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
