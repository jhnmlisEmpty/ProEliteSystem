<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
    <div class="w-full max-w-5xl bg-slate-900 bg-opacity-90 rounded-lg shadow-lg p-8 lg:p-12 flex flex-col lg:flex-row">
        <!-- Main Container -->
        <div class="flex gap-12 items-center">
            <!-- Left Side - Logo & Branding -->
            <div class="hidden lg:flex w-1/2 flex-col items-center justify-center">
                <div class="overflow-hidden flex items-center justify-center">
                    <img 
                        src="{{ asset('logo.png') }}" 
                        alt="Pro Elite Logo" 
                        class="w-full h-full object-contain p-8"
                    >
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full lg:w-1/2 max-w-md">
                <!-- Sign In Header -->
                <h2 class="text-center text-3xl font-bold text-yellow-400 italic tracking-tight block mb-8">PRO ELITE SYSTEM</h2>
                <!-- Login Form -->
                <form wire:submit.prevent="login" class="space-y-6">
                    <!-- Username -->
                    <div>
                        <label for="email" class="block text-gray-300 text-sm font-semibold mb-2 uppercase tracking-wider">
                            Username
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            wire:model="email"
                            class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition"
                            placeholder="Enter your email"
                        >
                        @error('email') 
                            <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-gray-300 text-sm font-semibold mb-2 uppercase tracking-wider">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            wire:model="password"
                            class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition"
                            placeholder="••••••••••••"
                        >
                        @error('password') 
                            <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="remember"
                            id="remember"
                            class="w-4 h-4 border border-slate-600 rounded bg-slate-700 checked:bg-yellow-400 checked:border-yellow-400 focus:ring-2 focus:ring-yellow-400 cursor-pointer"
                        >
                        <span class="ml-2 text-sm text-gray-400">Remember me</span>
                    </div>

                    <!-- Sign In Button -->
                    <button 
                        type="submit"
                        class="w-full py-3 bg-yellow-400 text-slate-900 font-semibold rounded hover:bg-yellow-500 transition duration-200 mt-8"
                    >
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
