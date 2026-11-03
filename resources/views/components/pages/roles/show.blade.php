
<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public Role $role;

    /**
     * ID permission yang dipilih pada checkbox.
     *
     * @var array<int>
     */
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        abort_unless(
            Auth::user()->can('roles.view'),
            403
        );

        abort_unless(
            $role->guard_name === 'web',
            404
        );

        $this->role = $role;

        $this->selectedPermissions = $role
            ->permissions()
            ->pluck('permissions.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    #[Computed]
    public function permissionGroups()
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return explode('.', $permission->name)[0];
            });
    }

    #[Computed]
    public function canAssignPermissions(): bool
    {
        return Auth::user()->can('permissions.assign');
    }

    public function savePermissions(): void
    {
        abort_unless(
            Auth::user()->can('permissions.assign'),
            403
        );

        $validated = $this->validate([
            'selectedPermissions' => ['present', 'array'],
            'selectedPermissions.*' => [
                'required',
                'integer',
                'distinct',
                'exists:permissions,id',
            ],
        ]);

        $permissionIds = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn(
                'id',
                $validated['selectedPermissions']
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($permissionIds) !== count($validated['selectedPermissions'])) {
            throw ValidationException::withMessages([
                'selectedPermissions' =>
                    'Terdapat permission yang tidak valid untuk guard web.',
            ]);
        }

        if (
            $this->role->name === 'Super Admin'
            && count($permissionIds) !== Permission::query()
                ->where('guard_name', 'web')
                ->count()
        ) {
            throw ValidationException::withMessages([
                'selectedPermissions' =>
                    'Permission Super Admin tidak boleh dikurangi pada tahap ini.',
            ]);
        }

        DB::transaction(function () use ($permissionIds) {
            $this->role->syncPermissions($permissionIds);
        });

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $this->selectedPermissions = $permissionIds;

        unset($this->permissionGroups);

        session()->flash(
            'success',
            'Permission role berhasil diperbarui.'
        );
    }
};
?>



<div class="p-6">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <a
            href="{{ route('roles.index') }}"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
        >
            &larr; Kembali ke Roles
        </a>

        <h1 class="mt-3 text-2xl font-bold text-gray-800">
            Detail Role
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Tinjau dan kelola permission role.
        </p>
    </div>

    @can('roles.update')
        @if ($role->name !== 'Super Admin')
            <a
                href="{{ route('roles.edit', $role->id) }}"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Edit Role
            </a>
        @endif
    @endcan
</div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm text-gray-500">Nama Role</p>

        <h2 class="mt-1 text-xl font-semibold text-gray-800">
            {{ $role->name }}
        </h2>

        <p class="mt-3 text-sm text-gray-500">
            Guard: {{ $role->guard_name }}
        </p>

        <p class="mt-1 text-sm text-gray-500">
            Jumlah permission saat ini:
            <span class="font-semibold text-gray-800">
                {{ $role->permissions()->count() }}
            </span>
        </p>
    </div>

    <form wire:submit="savePermissions">
        <div class="space-y-5">
            @foreach ($this->permissionGroups as $module => $permissions)
                <section
                    wire:key="module-{{ $module }}"
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                >
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h2 class="font-semibold capitalize text-gray-800">
                            {{ str_replace('_', ' ', $module) }}
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $permissions->count() }} permission
                        </p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($permissions as $permission)
                            <label
                                wire:key="permission-{{ $permission->id }}"
                                class="flex items-center gap-3 px-6 py-4
                                    {{ $this->canAssignPermissions
                                        ? 'cursor-pointer hover:bg-gray-50'
                                        : 'cursor-default' }}"
                            >
                                <input
                                    type="checkbox"
                                    wire:model="selectedPermissions"
                                    value="{{ $permission->id }}"
                                    @disabled(! $this->canAssignPermissions)
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-60"
                                >

                                <span class="text-sm text-gray-700">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        @if ($this->canAssignPermissions)
            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('roles.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </a>

                
                
<button
    type="submit"
    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
>
    Simpan Permission
</button>


            </div>
        @else
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Anda hanya dapat melihat permission. Anda tidak memiliki hak untuk mengubahnya.
            </div>
        @endif

        @error('selectedPermissions')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('selectedPermissions.*')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>
</div>
