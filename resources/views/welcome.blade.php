<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $gymName ?? 'VYPER' }} - Sistema de Gestión</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>localStorage.removeItem('theme'); document.documentElement.classList.remove('dark');</script>
</head>
<body class="font-sans antialiased text-[#171A20] dark:text-white bg-[#FFFFFF] dark:bg-[#000000] transition-colors duration-500 overflow-hidden" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div class="relative w-full h-screen flex flex-col justify-between">
        
        <!-- Top Navigation -->
        <header class="relative w-full px-8 py-8 flex justify-between items-center">
            <div class="text-[20px] font-bold tracking-[4px] uppercase text-[#171A20] dark:text-white transition-colors duration-500">
                {{ $gymName ?? 'VYPER' }}
            </div>
            
        </header>

        <!-- Hero Content (Original Text Restored) -->
        <main class="relative flex flex-col items-center text-center px-4 w-full -mt-16">
            <h1 class="text-[64px] sm:text-[80px] font-medium tracking-tight leading-none mb-6 text-[#171A20] dark:text-white transition-colors duration-500">
                El control total.
            </h1>
            <p class="text-[18px] sm:text-[22px] font-medium tracking-wide max-w-lg text-[#5C5E62] dark:text-gray-400 dark:text-white/70 transition-colors duration-500">
                Sistema Integral de Gestión Deportiva
            </p>
        </main>

        <!-- Bottom Action Area -->
        <div class="relative w-full flex flex-col items-center pb-12 px-4">
            <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md justify-center mb-12">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="w-full sm:w-[264px] h-[40px] flex items-center justify-center bg-[#171A20] text-white hover:bg-[#393C41] dark:bg-white dark:text-[#171A20] dark:hover:bg-gray-200 text-[14px] font-medium rounded-[4px] transition-colors duration-300">
                           Ir al Panel
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="w-full sm:w-[264px] h-[40px] flex items-center justify-center bg-[#171A20] text-white hover:bg-[#393C41] dark:bg-white dark:text-[#171A20] dark:hover:bg-gray-200 text-[14px] font-medium rounded-[4px] transition-colors duration-300">
                            Iniciar sesión
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Footer -->
            <footer class="flex items-center gap-6 text-[12px] font-normal text-[#8E8E8E] dark:text-white/50 transition-colors duration-500">
                <span>&copy; {{ date('Y') }} {{ $gymName ?? 'VYPER' }}</span>
                <a href="https://www.irangarcia.dev" target="_blank" rel="noopener noreferrer" class="hover:text-[#171A20] dark:text-white dark:hover:text-white transition-colors">
                    Ing. Iran Garcia
                </a>
            </footer>
        </div>

    </div>
</body>
</html>








