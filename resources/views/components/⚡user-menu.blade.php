<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public bool $open = false;

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return $this->redirectRoute('login');
    }
};
?>

<div
    x-data="{ open: @entangle('open') }"
    class="relative"
>
    {{-- User Button --}}
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-3"
    >
        <div class="text-right">
            <div class="font-medium">
                {{ auth()->user()->name }}
            </div>

            <div class="text-sm text-slate-500">
                {{ auth()->user()->email }}
            </div>
        </div>

        <span class="text-xl">
            ▼
        </span>
    </button>


    {{-- Dropdown --}}
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 mt-4 w-72 overflow-hidden rounded-xl border bg-white shadow-xl"
    >

        {{-- User Information --}}
        <div class="px-6 py-5">
            <div class="text-xl font-semibold">
                {{ auth()->user()->name }}
            </div>

            <div class="mt-1 text-slate-500">
                {{ auth()->user()->email }}
            </div>
        </div>


        {{-- Logout --}}
        <div class="border-t">
            <button
                type="button"
                wire:click="logout"
                wire:confirm="Apakah Anda yakin ingin logout?"
                class="w-full px-6 py-5 text-left text-lg text-red-600 hover:bg-red-50"
            >
                Logout
            </button>
        </div>

    </div>
</div>