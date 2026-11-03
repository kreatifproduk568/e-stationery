<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';


    /*
    |--------------------------------------------------------------------------
    | LIFECYCLE
    |--------------------------------------------------------------------------
    */

    public function mount(User $user): void
    {
        $this->authorize('users.update');

        $this->user = $user;

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles()
            ->where('guard_name', 'web')
            ->value('name') ?? '';

        $this->authorizeTargetUser();
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    private function authorizeTargetUser(): void
    {
        $actor = auth()->user();

        if (
            $this->user->hasRole('Super Admin')
            && ! $actor->hasRole('Super Admin')
        ) {
            abort(403, 'Anda tidak berwenang mengubah akun Super Admin.');
        }

        if (
            $this->user->is($actor)
            && $actor->hasRole('Super Admin')
        ) {
            // Super Admin boleh mengubah profilnya sendiri.
            // Perlindungan perubahan role dilakukan di save().
        }
    }


    /*
    |--------------------------------------------------------------------------
    | COMPUTED DATA
    |--------------------------------------------------------------------------
    */

    public function getAvailableRolesProperty()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->when(
                ! auth()->user()->hasRole('Super Admin'),
                fn ($query) => $query->where('name', '!=', 'Super Admin')
            )
            ->orderBy('name')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $this->authorize('users.update');

        $this->authorizeTargetUser();

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8),
            ],

            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $selectedRole = Role::query()
            ->where('name', $validated['role'])
            ->where('guard_name', 'web')
            ->firstOrFail();

        $actor = auth()->user();

        if (
            $selectedRole->name === 'Super Admin'
            && ! $actor->hasRole('Super Admin')
        ) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak berwenang memberikan role Super Admin.',
            ]);
        }

        // Cegah Super Admin menghapus role Super Admin dari dirinya sendiri.
        if (
            $this->user->is($actor)
            && $this->user->hasRole('Super Admin')
            && $selectedRole->name !== 'Super Admin'
        ) {
            throw ValidationException::withMessages([
                'role' => 'Role Super Admin tidak dapat dihapus dari akun sendiri.',
            ]);
        }

        DB::transaction(function () use ($validated, $selectedRole) {
            $attributes = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            // Password lama tetap dipertahankan jika input kosong.
            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }

            $this->user->update($attributes);

            $this->user->syncRoles([$selectedRole]);
        });

        session()->flash('success', 'User berhasil diperbarui.');

        $this->redirectRoute('users.index');
    }
};
?>

<div class="space-y-6 p-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Edit User
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Perbarui profil pengguna dan role aksesnya.
        </p>
    </div>

    {{-- FORM --}}
    <div class="max-w-3xl rounded-xl border border-slate-200
                bg-white p-6 shadow-sm">

        <form wire:submit="save" class="space-y-5">

            {{-- NAME --}}
            <div>
                <label for="name"
                       class="mb-2 block text-sm font-medium text-slate-700">
                    Nama Lengkap
                </label>

                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    autocomplete="name"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2
                           focus:border-indigo-500 focus:outline-none
                           focus:ring-2 focus:ring-indigo-200"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <label for="email"
                       class="mb-2 block text-sm font-medium text-slate-700">
                    Email
                </label>

                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2
                           focus:border-indigo-500 focus:outline-none
                           focus:ring-2 focus:ring-indigo-200"
                >

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div>
                <label for="password"
                       class="mb-2 block text-sm font-medium text-slate-700">
                    Password Baru (Opsional)
                </label>

                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    autocomplete="new-password"
                    placeholder="Kosongkan jika tidak ingin mengganti password"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2
                           focus:border-indigo-500 focus:outline-none
                           focus:ring-2 focus:ring-indigo-200"
                >

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PASSWORD CONFIRMATION --}}
            <div>
                <label for="password_confirmation"
                       class="mb-2 block text-sm font-medium text-slate-700">
                    Konfirmasi Password Baru
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    wire:model="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Ulangi password baru"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2
                           focus:border-indigo-500 focus:outline-none
                           focus:ring-2 focus:ring-indigo-200"
                >

                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ROLE --}}
            <div>
                <label for="role"
                       class="mb-2 block text-sm font-medium text-slate-700">
                    Role
                </label>

                <select
                    id="role"
                    wire:model="role"
                    class="w-full rounded-lg border border-slate-300 bg-white
                           px-4 py-2 focus:border-indigo-500 focus:outline-none
                           focus:ring-2 focus:ring-indigo-200"
                >
                    <option value="">Pilih role</option>

                    @foreach ($this->availableRoles as $availableRole)
                        <option value="{{ $availableRole->name }}">
                            {{ $availableRole->name }}
                        </option>
                    @endforeach
                </select>

                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">

                <a
                    href="{{ route('users.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2
                           text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="rounded-lg bg-slate-900 px-5 py-2 text-sm
                           font-medium text-white hover:bg-slate-800
                           disabled:cursor-not-allowed disabled:opacity-50"
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

</div>
