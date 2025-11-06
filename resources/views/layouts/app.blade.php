<!DOCTYPE html>
{{-- Usamos el idioma configurado --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Título dinámico usando $globalSettings o el nombre de la app por defecto --}}
        <title>{{ $globalSettings['gym_name'] ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && Storage::disk('public')->exists($globalSettings['gym_logo']))
            <link rel="icon" href="{{ Storage::url($globalSettings['gym_logo']) }}">
        @else
            {{-- Puedes poner un favicon por defecto aquí si quieres --}}
            {{-- <link rel="icon" href="/favicon.ico"> --}}
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex flex-col">
            <div class="flex-grow">
                {{-- Incluye la navegación (que ya usa $globalSettings para el logo) --}}
                @include('layouts.navigation')

                @if (isset($header))
                    <header class="bg-white shadow print:hidden">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>

            <footer class="w-full text-center text-sm text-gray-500 mt-8 pb-4 shrink-0 print:hidden">
                <p>
                    Desarrollado por
                    <a href="https://irangarcia.mx" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-gray-900 hover:underline">
                        Ing. Bryan Irán García Gutiérrez
                    </a>
                </p>
                <p class="mt-1">
                    <a href="https://irangarcia.mx" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline mx-2">
                        Sitio Web
                    </a> |
                    <a href="https://www.instagram.com/irangarcia93/" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline mx-2">
                        Instagram
                    </a>
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    &copy; {{ date('Y') }} Todos los Derechos Reservados.
                </p>
            </footer>

        </div>
    </body>
</html>