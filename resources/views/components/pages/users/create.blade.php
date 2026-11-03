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

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';


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
    | LIFECYCLE
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->authorize('users.create');
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $this->authorize('users.create');

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
                'unique:users,email',
            ],

            'password' => [
                'required',
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

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            $selectedRole->name === 'Super Admin'
            && ! auth()->user()->hasRole('Super Admin')
        ) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak berwenang memberikan role Super Admin.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE TO DATABASE
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($validated, $selectedRole) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $user->assignRole($selectedRole);
        });

        /*
        |--------------------------------------------------------------------------
        | FLASH MESSAGE
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'User berhasil ditambahkan.'
        );

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        $this->redirectRoute('users.index');
    }
};
?>

<div class="space-y-6 p-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Tambah User
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Buat akun pengguna baru dan tentukan role aksesnya.
        </p>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div
            role="status"
            class="rounded-lg border border-green-200 bg-green-50
                   px-4 py-3 text-sm text-green-700"
        >
            {{ session('success') }}
        </div>
    @endif

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
                    placeholder="Masukkan nama lengkap"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
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
                    placeholder="nama@example.com"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
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
                    Password
                </label>

                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
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
                    Konfirmasi Password
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    wire:model="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                    aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
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
                    aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
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

                <p class="mt-1 text-xs text-slate-500">
                    Pilih role sesuai tanggung jawab pengguna.
                </p>
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
                        Simpan User
                    </span>

                    <span wire:loading wire:target="save">
                        Menyimpan...
                    </span>
                </button>

            </div>

        </form>

    </div>

</div>