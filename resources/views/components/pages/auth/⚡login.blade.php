<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public string $password = '';

public function login()
{
    $credentials = $this->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials)) {

        $this->addError(
            'email',
            'Email atau password tidak sesuai.'
        );

        return;
    }

    request()->session()->regenerate();

    return $this->redirectRoute('dashboard');
}
};
?>

<div class="min-h-screen flex items-center justify-center bg-slate-950 px-4">

    <div class="w-full max-w-md">

        <div class="rounded-3xl bg-white p-8 shadow-2xl">

            <div class="mb-8 text-center">

                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white">
                    ES
                </div>

                <h1 class="text-2xl font-bold text-slate-900">
                    E-Stationery
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Smart Stationery Store
                </p>

            </div>

            <h2 class="mb-1 text-xl font-semibold text-slate-900">
                Welcome Back 👋
            </h2>

            <p class="mb-6 text-sm text-slate-500">
                Silakan Login untuk melanjutkan.
            </p>

            <form wire:submit="login" class="space-y-5">

                <div>

                    <label
                        for="email"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3"
                        placeholder="nama@email.com"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="password"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        wire:model="password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3"
                        placeholder="Masukkan password"
                    >

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800"
                >
                    Login
                </button>

            </form>

        </div>

    </div>

</div>