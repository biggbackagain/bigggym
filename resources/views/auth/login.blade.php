<x-guest-layout>
    <div class="mb-10 text-center">
        <h1 class="text-[32px] font-medium text-[#171A20] dark:text-white tracking-tight transition-colors duration-500">Iniciar sesión</h1>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[14px] font-medium text-[#171A20] dark:text-white mb-2 transition-colors duration-500">Correo electrónico</label>
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-[14px] font-medium text-[#171A20] dark:text-white transition-colors duration-500">Contraseña</label>
                @if (Route::has('password.request'))
                    <a class="text-[13px] text-[#5C5E62] dark:text-gray-400 dark:text-[#8E8E8E] hover:text-[#171A20] dark:text-white dark:hover:text-white transition-colors underline underline-offset-4 decoration-transparent hover:decoration-[#171A20] dark:hover:decoration-white" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded-[2px] border-[#EEEEEE] dark:border-[#333333] dark:border-[#393C41] text-[#171A20] dark:text-white shadow-none focus:ring-[#171A20] dark:focus:ring-white bg-[#F4F4F4] dark:bg-[#111111] dark:bg-[#171A20]" name="remember">
                <span class="ms-2 text-[14px] text-[#5C5E62] dark:text-gray-400 dark:text-white/70">Mantener sesión iniciada</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full h-[40px] flex items-center justify-center bg-[#171A20] text-white hover:bg-[#393C41] dark:bg-white dark:text-[#171A20] dark:hover:bg-gray-200 text-[14px] font-medium rounded-[4px] transition-colors duration-300 mt-2">
            Iniciar sesión
        </button>
    </form>
</x-guest-layout>








