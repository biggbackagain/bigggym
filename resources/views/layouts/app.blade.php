<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $globalSettings['gym_name'] ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && Storage::disk('public')->exists($globalSettings['gym_logo']))
            <link rel="icon" href="{{ (!empty($globalSettings['gym_logo']) && str_starts_with($globalSettings['gym_logo'], 'http') ? $globalSettings['gym_logo'] : Storage::url($globalSettings['gym_logo'])) }}">
        @endif

        {{-- ¡ESTA LÍNEA ES CRUCIAL y necesita 'npm run dev' para funcionar! --}}
        <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        

        {{-- Aquí se inyectan los estilos de impresión --}}
        @stack('styles')
    <script>localStorage.removeItem('theme'); document.documentElement.classList.remove('dark');</script>
    </head>
    <body class="font-sans antialiased text-[#393C41] dark:text-gray-300 dark:text-white bg-white dark:bg-[#000000] transition-colors duration-500" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        <div class="min-h-screen flex flex-col bg-white dark:bg-[#000000] transition-colors duration-500">
            <div class="flex-grow">
                {{-- Incluye la navegación --}}
                @include('layouts.navigation')

                @if (isset($header))
                    <header class="bg-white dark:bg-[#000000] border-b border-[#EEEEEE] dark:border-[#333333] print:hidden">
                        <div class="max-w-[1383px] mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>

            <footer class="w-full text-center text-sm text-gray-500 dark:text-gray-400 mt-8 pb-4 shrink-0 print:hidden">
                <p>
                    Desarrollado por
                    <a href="https://www.irangarcia.dev" target="_blank" rel="noopener noreferrer" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white hover:underline">
                        Ing. Iran Garcia
                    </a>
                </p>
                <p class="mt-1">
                    <a href="https://www.irangarcia.dev" target="_blank" rel="noopener noreferrer" class="text-[#171A20] dark:text-white underline underline-offset-4 hover:underline mx-2">
                        Sitio Web
                    </a>
                    |
                    <a href="https://www.instagram.com/irangarcia93/" target="_blank" rel="noopener noreferrer" class="text-[#171A20] dark:text-white underline underline-offset-4 hover:underline mx-2">
                        Instagram
                    </a>
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    &copy; {{ date('Y') }} Todos los Derechos Reservados.
                </p>
            </footer>

        </div>
        
        {{-- Aquí se inyectan scripts específicos de la página (como el de Alpine.js del POS) --}}
        @stack('scripts')
    </body>
</html>











