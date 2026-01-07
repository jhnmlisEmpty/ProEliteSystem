<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ProEliteSystem') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="bg-gray-900 shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false, userMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <img src="{{ asset('logo.png') }}" alt="PRO ELITE" class="h-8 w-auto">
                        <div>
                            <span class="text-md font-bold text-yellow-400 italic tracking-tight block">PRO ELITE SYSTEM - {{ Auth::user()->name }}</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('/') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-home class="w-5 h-5 inline-block mr-1" />
                            Dashboard
                        </a>
                        <a href="{{ route('orders.index') }}" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('orders*') || request()->is('pos*') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 inline-block mr-1" />
                            Orders
                        </a>
                        <a href="{{ route('board.index') }}" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('board*') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5 inline-block mr-1" />
                            Job Order Board
                        </a>
                        <a href="{{ route('products.index') }}" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('products*') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-cube class="w-5 h-5 inline-block mr-1" />
                            Products
                        </a>
                        <a href="{{ route('services.index') }}" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('services*') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-wrench-screwdriver class="w-5 h-5 inline-block mr-1" />
                            Services
                        </a>
                        <a href="{{ route('customers.index') }}" 
                           class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('customers*') ? 'bg-gray-700 text-white' : '' }}">
                            <x-heroicon-o-users class="w-5 h-5 inline-block mr-1" />
                            Customers
                        </a>
                        
                    </div>
                </div>

                <!-- Right side logout button -->
                <div class="hidden md:block">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 mr-1" />
                            Logout
                        </button>
                    </form>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <x-heroicon-o-bars-3 class="w-6 h-6" x-show="!mobileMenuOpen" />
                        <x-heroicon-o-x-mark class="w-6 h-6" x-show="mobileMenuOpen" style="display: none;" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden" x-show="mobileMenuOpen" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-home class="w-5 h-5 inline-block mr-1" />
                    Dashboard
                </a>
                <a href="{{ route('orders.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('orders*') || request()->is('pos*') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 inline-block mr-1" />
                    Orders
                </a>
                <a href="{{ route('board.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('board*') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5 inline-block mr-1" />
                    Job Order Board
                </a>
                <a href="{{ route('products.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('products*') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-cube class="w-5 h-5 inline-block mr-1" />
                    Products
                </a>
                <a href="{{ route('services.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('services*') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-wrench-screwdriver class="w-5 h-5 inline-block mr-1" />
                    Services
                </a>
                <a href="{{ route('customers.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->is('customers*') ? 'bg-gray-700 text-white' : '' }}">
                    <x-heroicon-o-users class="w-5 h-5 inline-block mr-1" />
                    Customers
                </a>
                <hr class="my-2 border-gray-700">
                <div class="px-3 py-2">
                    <p class="text-gray-300 text-sm font-medium mb-2">{{ Auth::user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 inline-block mr-1" />
                            Logout
                        </button>
                    </form>
                </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-400" />
                        <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-400" />
                        <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
