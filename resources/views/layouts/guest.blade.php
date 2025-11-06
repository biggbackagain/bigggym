<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Título dinámico --}}
        <title>{{ $globalSettings['gym_name'] ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && Storage::disk('public')->exists($globalSettings['gym_logo']))
            <link rel="icon" href="{{ Storage::url($globalSettings['gym_logo']) }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    {{-- Logo dinámico --}}
                    @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && Storage::disk('public')->exists($globalSettings['gym_logo']))
                        <img src="{{ Storage::url($globalSettings['gym_logo']) }}" alt="Logo" class="w-20 h-20 object-contain"> {{-- Usamos object-contain --}}
                    @else
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    @endif
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        {{-- Footer Opcional para página de login --}}
        <footer class="w-full text-center text-sm text-gray-500 mt-4 pb-4 shrink-0">
             <p>
                 Desarrollado por
                 <a href="https://irangarcia.mx" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-gray-900 hover:underline">
                     Ing. Bryan Irán García Gutiérrez
                 </a>
             </p>
             <p class="mt-1 text-xs text-gray-400">
                 &copy; {{ date('Y') }} Todos los Derechos Reservados.
             </p>
         </footer>
    </body>
</html>