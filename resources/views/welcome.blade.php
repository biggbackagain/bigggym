<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <title>Bienvenido a {{ $gymName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-900 text-white min-h-screen flex flex-col items-center justify-center relative overflow-hidden font-sans">
        
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=2070&auto=format&fit=crop" 
                 class="w-full h-full object-cover opacity-30 filter blur-[2px] scale-105" 
                 alt="Fondo BiggGym">
            <div class="absolute inset-0 bg-gradient-to-tr from-black via-gray-900/90 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-5xl w-full px-6 text-center flex-grow flex flex-col justify-center">
            
            <div class="mb-10 animate-fade-in-down">
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                
                <h1 class="text-6xl md:text-8xl font-black tracking-tighter text-white drop-shadow-2xl uppercase">
                    {{-- Lógica para separar la primera palabra y darle color a la segunda --}}
                    @php
                        $words = explode(' ', $gymName);
                        $firstWord = array_shift($words); // Saca la primera palabra
                        $restOfName = implode(' ', $words); // Junta el resto
                    @endphp

                    {{-- Muestra la primera palabra en Blanco --}}
                    {{ $firstWord }}
                    
                    {{-- Muestra el resto en color Indigo (si hay más palabras) --}}
                    @if(!empty($restOfName))
                        <span class="text-indigo-500">{{ $restOfName }}</span>
                    @endif
                </h1>
                
                <p class="mt-6 text-xl md:text-2xl text-gray-300 font-light max-w-2xl mx-auto leading-relaxed">
                    Sistema Integral de Gestión Deportiva y Punto de Venta.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-5 justify-center items-center mt-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-200 bg-indigo-600 rounded-full focus:outline-none hover:bg-indigo-700 hover:scale-105 shadow-lg hover:shadow-indigo-500/50 ring-2 ring-indigo-600 ring-offset-2 ring-offset-gray-900">
                           <span>Ir al Panel de Control</span>
                           <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="min-w-[160px] px-8 py-3 bg-white text-gray-900 hover:bg-gray-100 font-bold text-lg rounded-full transition-all shadow-xl transform hover:-translate-y-1 hover:shadow-2xl text-center">
                            Ingresar
                        </a>
                    @endauth
                @endif
            </div>
        </div>

        <div class="relative z-10 w-full py-6 text-center border-t border-white/10 bg-black/20 backdrop-blur-md">
            <p class="text-sm text-gray-400 font-medium">
                &copy; {{ date('Y') }} BiggGym System. Todos los derechos reservados.
            </p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest">
                Desarrollado por <span class="text-indigo-400 font-bold">Ing. Bryan Iran García Gutierrez</span>
            </p>
        </div>

    </body>
</html>