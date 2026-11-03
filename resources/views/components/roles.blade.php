<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $roleIdToDelete = null;
    public bool $showSuccess = true;

    public function mount(): void
    {
        abort_unless(
            Auth::user()->can('roles.view'),
            403
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->withCount(['users', 'permissions'])
            ->when(
                trim($this->search) !== '',
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . trim($this->search) . '%'
                )
            )
            ->orderBy('name')
            ->paginate(10);
    }

    
public function deleteRole(int $roleId): void
{
    abort_unless(
        Auth::user()->can('roles.delete'),
        403
    );

    $role = Role::query()
        ->where('guard_name', 'web')
        ->findOrFail($roleId);

    if ($role->name === 'Super Admin') {
        throw ValidationException::withMessages([
            'deleteRole' => 'Role Super Admin tidak boleh dihapus.',
        ]);
    }

    if ($role->users()->exists()) {
        throw ValidationException::withMessages([
            'deleteRole' =>
                'Role yang masih digunakan pengguna tidak boleh dihapus.',
        ]);
    }

    DB::transaction(function () use ($role) {
        $role->syncPermissions([]);
        $role->delete();
    });

    app(\Spatie\Permission\PermissionRegistrar::class)
        ->forgetCachedPermissions();

    // Tutup modal setelah penghapusan berhasil.
    $this->roleIdToDelete = null;

    // Bersihkan error validasi lama.
    $this->resetValidation('deleteRole');

    // Perbarui daftar role.
    unset($this->roles);

    session()->flash(
        'success',
        'Role berhasil dihapus.'
    );

    $this->showSuccess = true;
}



    public function confirmDelete(int $roleId): void
    {
        abort_unless(
            Auth::user()->can('roles.delete'),
            403
        );

        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            abort(403);
        }

        $this->roleIdToDelete = $role->id;
    }

    public function cancelDelete(): void
    {
        $this->roleIdToDelete = null;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }
};
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Role Management
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Kelola role dan tinjau pengguna serta permission yang terkait.
        </p>

        @can('roles.create')
    <a
        href="{{ route('roles.create') }}"
        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
    >
        + Tambah Role
    </a>
@endcan
    </div>

    
@if (session('success') && $showSuccess)
    <div
        class="mb-4 flex items-center justify-between gap-4 rounded-lg bg-green-100 p-4 text-green-800"
        role="status"
    >
        <span>{{ session('success') }}</span>

        <button
            type="button"
            wire:click="dismissSuccess"
            aria-label="Tutup pesan"
            title="Tutup pesan"
            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-green-800 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-600"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18 18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
@endif


    <div class="mb-4">
        <label for="search-role" class="sr-only">
            Cari role
        </label>

        <input
            id="search-role"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama role..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Role
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Pengguna
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Permissions
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse ($this->roles as $role)
                    <tr wire:key="role-{{ $role->id }}">
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="font-medium text-gray-800">
                                {{ $role->name }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center text-gray-600">
                            {{ $role->users_count }}
                        </td>

                        <td class="px-6 py-4 text-center text-gray-600">
                            {{ $role->permissions_count }}
                        </td>
                       <td class="px-6 py-4 text-center">
    <div class="flex flex-wrap items-center justify-center gap-2">
        <a
            href="{{ route('roles.show', $role) }}"
            class="inline-flex items-center rounded-lg border border-indigo-300 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50"
        >
            Detail
        </a>

        @can('roles.delete')
            @if ($role->name !== 'Super Admin')
                <button
                    type="button"
                    {{-- wire:click="deleteRole({{ $role->id }})" --}}
                    wire:click="confirmDelete({{ $role->id }})"
                    {{-- wire:confirm="Apakah Anda yakin ingin menghapus role {{ $role->name }}?" --}}
                    wire:loading.attr="disabled"
                    wire:target="deleteRole({{ $role->id }})"
                    class="inline-flex items-center rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                >
                    Hapus
                </button>
            @endif
        @endcan
    </div>
</td>
                        
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="3"
                            class="px-6 py-10 text-center text-sm text-gray-500"
                        >
                            Tidak ada role yang sesuai dengan pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->roles->links() }}
    </div>


    @if ($roleIdToDelete !== null)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
        wire:key="delete-role-modal"
    >
        <div
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-role-title"
        >
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg
                        class="h-6 w-6 text-red-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m0 3h.008M10.29 3.86 1.82 18.14A2 2 0 0 0 3.53 21h16.94a2 2 0 0 0 1.71-2.86L13.71 3.86a2 2 0 0 0-3.42 0Z"
                        />
                    </svg>
                </div>

                <div>
                    <h2
                        id="delete-role-title"
                        class="text-lg font-bold text-gray-900"
                    >
                        Hapus Role?
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Apakah Anda yakin ingin menghapus role
                        <strong>
                            {{ \Spatie\Permission\Models\Role::find($roleIdToDelete)?->name }}
                        </strong>?
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        Role yang masih digunakan pengguna tidak dapat dihapus.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    wire:click="cancelDelete"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    wire:click="deleteRole({{ $roleIdToDelete }})"
                    wire:loading.attr="disabled"
                    wire:target="deleteRole"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="deleteRole">
                        Ya, Hapus
                    </span>

                    <span wire:loading wire:target="deleteRole">
                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif


</div>
