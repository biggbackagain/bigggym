<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $globalSettings['gym_name'] ?? config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
    <script>localStorage.removeItem('theme'); document.documentElement.classList.remove('dark');</script>
    </head>
    <body class="font-sans text-[#393C41] dark:text-gray-300 dark:text-white antialiased bg-white dark:bg-[#000000] transition-colors duration-500" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        
        <div class="min-h-screen flex flex-col w-full bg-white dark:bg-[#000000] transition-colors duration-500">
            
            <!-- Minimalist Top Navigation -->
            <header class="w-full px-6 py-4 flex justify-between items-center">
                <a href="/" class="flex items-center gap-3">
                    @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && (str_starts_with($globalSettings['gym_logo'], 'http') || Storage::disk('public')->exists($globalSettings['gym_logo'])))
                        <img src="{{ (!empty($globalSettings['gym_logo']) && str_starts_with($globalSettings['gym_logo'], 'http') ? $globalSettings['gym_logo'] : Storage::url($globalSettings['gym_logo'])) }}" alt="Logo" class="w-8 h-8 object-contain">
                    @else
                    <span class="text-[15px] font-medium text-[#171A20] dark:text-white uppercase tracking-[2px]">{{ $globalSettings['gym_name'] ?? 'VYPER' }}</span>
                    @endif
                </a>
                
                <!-- Dark Mode Toggle -->
                
            </header>

            <!-- Form Container -->
            <main class="flex-grow flex flex-col items-center justify-center p-6">
                <div class="w-full max-w-[340px]">
                    {{ $slot }}
                </div>
            </main>

            <!-- Minimal Footer -->
            <footer class="p-6 text-center text-[12px] text-[#5C5E62] dark:text-gray-400 flex flex-col sm:flex-row justify-center gap-4">
                <span>&copy; {{ date('Y') }} BiggGym System</span>
                <span class="hidden sm:inline">|</span>
                <a href="https://www.irangarcia.dev" target="_blank" rel="noopener noreferrer" class="hover:text-[#171A20] dark:text-white transition-colors underline underline-offset-4 decoration-transparent hover:decoration-[#171A20]">
                    Ing. Iran Garcia
                </a>
            </footer>
            
        </div>
    </body>
</html>









