<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $title ?? config('app.name') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white">

            {{-- Logo --}}
            <div class="border-b border-gray-700 p-6">
                <h1 class="text-xl font-bold">
                    SIJA Stationery
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Backend System
                </p>
            </div>

            {{-- Navigation --}}
            <nav class="p-4">

                <p class="mb-2 text-xs font-semibold uppercase text-gray-500">
                    Main
                </p>

                <a
                    href="/dashboard"
                    class="mb-2 block rounded-lg bg-gray-800 px-4 py-2 text-sm"
                >
                    Dashboard
                </a>

                <p class="mb-2 mt-6 text-xs font-semibold uppercase text-gray-500">
                    Master Data
                </p>

                <a
                    href="{{ route('categories.index') }}"
                    class="{{ request()->routeIs('categories.*')
                        ? 'block rounded-lg bg-slate-800 px-4 py-3 text-white'
                        : 'block rounded-lg px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    Categories
                </a>

                <a
                    href="{{ route('brands.index') }}"
                    class="{{ request()->routeIs('brands.*')
                        ? 'block rounded-lg bg-slate-800 px-4 py-3 text-white'
                        : 'block rounded-lg px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    Brands
                </a>

                <a
                    href="{{ route('products.index') }}"
                     class="{{ request()->routeIs('products.*')
                        ? 'block rounded-lg bg-slate-800 px-4 py-3 text-white'
                        : 'block rounded-lg px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    Products
                </a>

                <a
                    href="users"
                    class="mb-1 block rounded-lg px-4 py-2 text-sm text-gray-300 hover:bg-gray-800"
                >
                    Users
                </a>

                
                @if (auth()->user()->can('roles.view'))
                    <p class="mb-2 mt-6 text-xs font-semibold uppercase text-gray-500">
                        Access Management
                    </p>

                    <a
                        href="{{ route('roles.index') }}"
                        class="{{ request()->routeIs('roles.*')
                            ? 'block rounded-lg bg-slate-800 px-4 py-3 text-white'
                            : 'block rounded-lg px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        Roles
                    </a>
                @endif


            </nav>

        </aside>


        {{-- Main Area --}}
        <div class="flex flex-1 flex-col">

            {{-- Navbar --}}
            <header class="border-b bg-white">

                <div class="flex items-center justify-between px-6 py-4">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            Backend Dashboard
                        </h2>

                        <p class="text-sm text-gray-500">
                            SIJA Teaching Factory
                        </p>
                    </div>
<!-- 
                    <div class="text-sm text-gray-600">
                        Administrator
                    </div> -->

                    <livewire:user-menu />



                </div>

            </header>


            {{-- Content --}}
            <main class="flex-1">

                {{ $slot }}

            </main>

        </div>

    </div>

    @livewireScripts

</body>

</html>