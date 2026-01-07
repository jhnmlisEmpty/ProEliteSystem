<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="w-full max-w-xs">
        <!-- Logo -->
        <div class="flex justify-center mb-4">
            <img src="{{ asset('logo.png') }}" alt="Pro Elite Logo" class="h-14 w-14">
        </div>

        <!-- Company Name -->
        <h1 class="text-center text-lg font-semibold text-white mb-4">
            PRO ELITE SYSTEM
        </h1>

        <!-- Login Form Card -->
        <div class="bg-slate-800/80 rounded-lg shadow-2xl p-5">
            <form wire:submit.prevent="login" class="space-y-3">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-gray-300 text-xs mb-1">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        wire:model="email"
                        class="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="email@example.com"
                    >
                    @error('email') 
                        <span class="text-red-400 text-xs mt-0.5 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-300 text-xs mb-1">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        wire:model="password"
                        class="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="••••••••••••"
                    >
                    @error('password') 
                        <span class="text-red-400 text-xs mt-0.5 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="remember"
                        id="remember"
                        class="w-3.5 h-3.5 border-gray-400 rounded"
                    >
                    <span class="ml-1.5 text-xs text-gray-400">Remember me</span>
                </div>

                <!-- Login Button -->
                <div class="flex justify-end pt-1">
                    <button 
                        type="submit"
                        class="px-6 py-1.5 bg-white text-gray-900 text-sm font-semibold rounded hover:bg-gray-100 transition"
                    >
                        LOG IN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
