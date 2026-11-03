<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $userIdToDelete = null;

    #[Computed]
    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('users.view');
    }

    public function confirmDelete(string $userId): void
    {
        $this->authorize('users.delete');

        $user = User::query()->findOrFail($userId);

        $this->ensureCanDelete($user);

        $this->userIdToDelete = $user->id;
    }

    public function deleteUser(): void
    {
        $this->authorize('users.delete');

        if (! $this->userIdToDelete) {
            throw ValidationException::withMessages([
                'delete' => 'Pilih pengguna yang akan dihapus.',
            ]);
        }

        $user = User::query()->findOrFail($this->userIdToDelete);

        $this->ensureCanDelete($user);

        DB::transaction(function () use ($user) {
            // Lepaskan relasi Spatie sebelum menghapus pengguna.
            $user->syncRoles([]);
            $user->syncPermissions([]);

            $user->delete();
        });

        $this->userIdToDelete = null;

        unset($this->users);

        session()->flash('success', 'User berhasil dihapus.');
    }

    public function cancelDelete(): void
    {
        $this->userIdToDelete = null;
    }

    private function ensureCanDelete(User $user): void
    {
        if ($user->is(auth()->user())) {
            throw ValidationException::withMessages([
                'delete' => 'Anda tidak dapat menghapus akun sendiri.',
            ]);
        }

        if ($user->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'delete' => 'Akun Super Admin tidak dapat dihapus melalui fitur ini.',
            ]);
        }
    }
};
?>

<div class="space-y-6 p-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            User Management
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Kelola akun pengguna dan role E-Stationery.
        </p>
    </div>

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div
            role="status"
            class="rounded-lg border border-green-200 bg-green-50
                   px-4 py-3 text-sm text-green-700"
        >
            {{ session('success') }}
        </div>
    @endif

    {{-- DELETE VALIDATION MESSAGE --}}
    @error('delete')
        <div
            role="alert"
            class="rounded-lg border border-red-200 bg-red-50
                   px-4 py-3 text-sm text-red-700"
        >
            {{ $message }}
        </div>
    @enderror

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

        {{-- SEARCH --}}
        <div class="mb-5">
            <label
                for="search-users"
                class="mb-2 block text-sm font-medium text-slate-700"
            >
                Cari pengguna
            </label>

            <input
                id="search-users"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau email..."
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm
                       focus:border-indigo-500 focus:outline-none focus:ring-2
                       focus:ring-indigo-200"
            >
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Email</th>
                        <th class="px-4 py-3 font-semibold">Role</th>

                        @if (
                            auth()->user()->can('users.update')
                            || auth()->user()->can('users.delete')
                        )
                            <th class="px-4 py-3 text-right font-semibold">
                                Aksi
                            </th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->users as $user)
                        <tr
                            wire:key="user-{{ $user->id }}"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $user->name }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $user->email }}
                            </td>

                            <td class="px-4 py-3">
                                @forelse ($user->roles as $role)
                                    <span
                                        class="mr-1 inline-flex rounded-full
                                               bg-indigo-50 px-2.5 py-1
                                               text-xs font-medium text-indigo-700"
                                    >
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400">
                                        Belum ada role
                                    </span>
                                @endforelse
                            </td>

                            @if (
                                auth()->user()->can('users.update')
                                || auth()->user()->can('users.delete')
                            )
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">

                                        @can('users.update')
                                            <a
                                                href="{{ route('users.edit', $user->id) }}"
                                                class="inline-flex items-center rounded-lg
                                                       border border-slate-300 px-3 py-1.5
                                                       text-sm font-medium text-slate-700
                                                       hover:bg-slate-50"
                                            >
                                                Edit
                                            </a>
                                        @endcan

                                        @can('users.delete')
                                            <button
                                                type="button"
                                                wire:click="confirmDelete('{{ $user->id }}')"
                                                class="inline-flex items-center rounded-lg
                                                       border border-red-200 px-3 py-1.5
                                                       text-sm font-medium text-red-700
                                                       hover:bg-red-50"
                                            >
                                                Hapus
                                            </button>
                                        @endcan

                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="{{
                                    auth()->user()->can('users.update')
                                    || auth()->user()->can('users.delete')
                                        ? 4 : 3
                                }}"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Tidak ada pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-5">
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- DELETE CONFIRMATION --}}
    @if ($userIdToDelete)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center
                   bg-slate-900/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-dialog-title"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">

                <h2
                    id="delete-dialog-title"
                    class="text-lg font-bold text-slate-800"
                >
                    Konfirmasi Hapus User
                </h2>

                <p class="mt-3 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus akun ini?
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="rounded-lg border border-slate-300 px-4 py-2
                               text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        wire:click="deleteUser"
                        wire:loading.attr="disabled"
                        wire:target="deleteUser"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm
                               font-medium text-white hover:bg-red-700
                               disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="deleteUser">
                            Ya, Hapus
                        </span>

                        <span wire:loading wire:target="deleteUser">
                            Menghapus...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
