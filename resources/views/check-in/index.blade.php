<x-app-layout>
    <!-- Main Container -->
    <section id="access-station" class="w-full bg-white dark:bg-[#000000] flex flex-col transition-all duration-300" data-endpoint="{{ route('check-in.biometric') }}" style="min-height: calc(100vh - 65px);">
        
        <!-- Topbar -->
        <div class="w-full max-w-[1383px] mx-auto px-6 py-6 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-[20px] font-medium text-[#171A20] dark:text-white tracking-tight hover:opacity-70 transition-opacity">
                <span class="text-[16px] font-medium text-[#171A20] dark:text-white uppercase tracking-[2px]">ACCESO</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('members.create') }}" class="text-[14px] font-medium text-[#171A20] dark:text-white bg-transparent hover:bg-[#F4F4F4] dark:bg-[#111111] px-4 py-2 rounded-[4px] transition-colors">Registrar socio</a>
            <button
            </div> type="button" id="kiosk-toggle" class="text-[14px] font-medium text-[#171A20] dark:text-white bg-transparent hover:bg-[#F4F4F4] dark:bg-[#111111] px-4 py-2 rounded-[4px] transition-colors">
                <span class="hidden sm:inline">Pantalla completa</span>
                <span class="sm:hidden">Ampliar</span>
            </button>
        </div>

        <div class="flex-grow w-full max-w-[1383px] mx-auto px-6 pb-12 flex items-center justify-center">
            
            <!-- Hidden force-compile classes for JS -->
            <div class="hidden bg-[#E6F4EA] text-[#137333] bg-[#FCE8E6] text-[#A50E0E]"></div>

            <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 items-center">
                
                <!-- Left Column: Info & Manual Entry -->
                <div class="lg:col-span-5 flex flex-col gap-10">
                    <div>
                        <h1 class="text-[40px] font-medium text-[#171A20] dark:text-white leading-[1.2] mb-4">
                            Un paso más.<br>Una mejor versión.
                        </h1>
                        <p class="text-[14px] text-[#393C41] dark:text-gray-300 leading-[1.43] max-w-md">
                            {{ $biometricsEnabled ? 'Mira a la cámara para verificar tu membresía y comenzar tu entrenamiento.' : 'Ingresa tu código de socio para verificar tu membresía y comenzar tu entrenamiento.' }}
                        </p>
                    </div>

                    @if($biometricsEnabled)
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-4 text-[14px] text-[#393C41] dark:text-gray-300">
                            <span class="text-[12px] font-medium text-[#8E8E8E] w-4">01</span>
                            <p>Acércate y coloca tu rostro dentro de la guía.</p>
                        </div>
                        <div class="flex items-center gap-4 text-[14px] text-[#393C41] dark:text-gray-300">
                            <span class="text-[12px] font-medium text-[#8E8E8E] w-4">02</span>
                            <p>Mira de frente, con buena luz y sin cubrir tu rostro.</p>
                        </div>
                    </div>
                    @endif

                    <div class="{{ $biometricsEnabled ? 'pt-6 border-t border-[#EEEEEE] dark:border-[#333333] dark:border-gray-800' : '' }}">
                        <form method="POST" action="{{ route('check-in.store') }}" class="flex flex-col gap-4">
                            @csrf
                            <div>
                                <label for="member_code" class="block text-[14px] font-medium text-[#171A20] dark:text-white mb-2">Acceso con código de socio</label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input type="text" id="member_code" name="member_code" value="{{ old('member_code') }}" placeholder="Ej. GYM-123" required autocomplete="off" class="flex-1 bg-[#F4F4F4] dark:bg-[#111111] border-transparent focus:bg-white dark:focus:bg-[#000000] focus:border-[#3E6AE1] focus:ring-1 focus:ring-[#3E6AE1] text-[14px] text-[#171A20] dark:text-white rounded-[4px] px-4 py-3 placeholder-[#8E8E8E] transition-colors">
                                    <button type="submit" class="w-full sm:w-[200px] h-[40px] bg-[#171A20] hover:bg-[#393C41] dark:bg-white dark:hover:bg-gray-200 text-white dark:text-[#171A20] text-[14px] font-medium rounded-[4px] transition-colors flex items-center justify-center">
                                        Verificar
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('member_code')" class="mt-2" />
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Camera & Result -->
                <div class="lg:col-span-7 flex flex-col gap-4">
                    
                    @if($biometricsEnabled)
                    <!-- Camera Container -->
                    <div class="relative w-full aspect-[16/9] lg:aspect-[4/3] bg-[#171A20] rounded-[12px] overflow-hidden">
                        
                        <video id="access-video" autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover transform -scale-x-100"></video>

                        <!-- Face Guide Overlay -->
                        <div class="absolute inset-[15%] sm:inset-[18%] md:inset-[20%] border border-[#EEEEEE] dark:border-[#333333] dark:border-gray-800 opacity-30 rounded-[12px] pointer-events-none transition-all" aria-hidden="true"></div>

                        <!-- Member Photo Overlay -->
                        <div id="member-photo-overlay" hidden class="absolute inset-0 z-20 bg-[#171A20]/80 flex items-center justify-center transition-opacity duration-300">
                            <div class="text-center flex flex-col items-center">
                                <img id="member-photo-img" src="" alt="Foto del socio" class="w-32 h-32 rounded-[12px] object-cover mb-4">
                                <p id="member-photo-name" class="text-white text-[22px] font-medium"></p>
                            </div>
                        </div>

                        <!-- Placeholder -->
                        <div id="camera-placeholder" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 bg-[#171A20] p-8 text-center">
                            <div>
                                <strong class="text-[22px] text-white font-medium">Listo cuando tú lo estés</strong>
                                <p class="text-[#8E8E8E] text-[14px] max-w-sm mt-2 mx-auto">Activa la cámara para verificar tu acceso mediante reconocimiento facial.</p>
                            </div>
                            <button type="button" id="camera-start" class="mt-4 px-8 h-[40px] bg-[#171A20] hover:bg-[#393C41] dark:bg-white dark:hover:bg-gray-200 text-white dark:text-[#171A20] text-[14px] font-medium rounded-[4px] transition-colors">
                                Activar cámara
                            </button>
                        </div>

                        <!-- Bottom Bar -->
                        <div class="absolute bottom-0 inset-x-0 p-6 flex items-center justify-between text-[14px] text-white bg-gradient-to-t from-[#171A20] to-transparent">
                            <span id="scan-message" role="status" aria-live="polite" class="font-medium opacity-90">Cámara apagada</span>
                            <button type="button" id="camera-stop" hidden class="text-[#8E8E8E] hover:text-white transition-colors">Pausar</button>
                        </div>
                    </div>
                    @endif

                    <!-- Result Panel -->
                    @if($biometricsEnabled)
                        <div id="access-result" class="flex items-center gap-4 p-5 bg-[#F4F4F4] dark:bg-[#111111] rounded-[12px] transition-colors duration-300 min-h-[90px]" role="status" aria-live="polite">
                            <div class="flex-1 min-w-0">
                                <h2 id="result-title" class="text-[17px] font-medium text-[#171A20] dark:text-white truncate">Te damos la bienvenida</h2>
                                <p id="result-message" class="text-[14px] text-[#5C5E62] dark:text-gray-400 mt-1 line-clamp-2">Aquí aparecerá el resultado de tu acceso.</p>
                                <p id="result-date" class="text-[12px] text-[#8E8E8E] mt-1"></p>
                            </div>
                        </div>
                    @else
                        <!-- HUGE Result Panel when Biometrics is Disabled -->
                        <div id="access-result" class="flex flex-col items-center justify-center p-12 bg-[#F4F4F4] dark:bg-[#111111] rounded-[12px] transition-colors duration-300 min-h-[400px] text-center w-full" role="status" aria-live="polite">
                            <!-- Predefined large photo element, hidden initially -->
                            <img id="result-photo" src="" alt="Foto del socio" class="w-48 h-48 rounded-[12px] object-cover shrink-0 mb-6 hidden shadow-sm">
                            
                            <div class="flex-1 min-w-0 w-full">
                                <h2 id="result-title" class="text-[28px] font-medium text-[#171A20] dark:text-white mb-2">Te damos la bienvenida</h2>
                                <p id="result-message" class="text-[16px] text-[#5C5E62] dark:text-gray-400 mt-2 px-4 leading-relaxed">Ingresa un código para verificar la membresía.</p>
                                <p id="result-date" class="text-[14px] text-[#8E8E8E] mt-4 font-medium uppercase tracking-[1px]"></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <script id="initial-access-result" type="application/json">@json(session('access_result'))</script>
        <script>window._biometricsEnabled = {{ $biometricsEnabled ? 'true' : 'false' }};</script>
    </section>

    <!-- Styles -->
    <style>
        #access-station:fullscreen {
            background-color: #FFFFFF;
        }
        #access-station:fullscreen > .max-w-\[1383px\]:first-child {
            display: none; /* Hide topbar in fullscreen */
        }
    </style>
</x-app-layout>












