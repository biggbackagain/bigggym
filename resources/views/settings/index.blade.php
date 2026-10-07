<x-app-layout>
    <x-slot name="header">
        {{ __('Configuración General') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            {{-- Mensajes de error de validación --}}
             @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg">
                    <p><strong>Error al guardar:</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-white">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Datos del Gimnasio</h3>

                        {{-- Nombre del Gimnasio --}}
                        <div>
                            <x-input-label for="gym_name" :value="__('Nombre del Gimnasio')" />
                            <x-text-input id="gym_name" class="block mt-1 w-full" type="text" name="gym_name" :value="old('gym_name', $settings['gym_name'] ?? '')" required autofocus />
                            <x-input-error :messages="$errors->get('gym_name')" class="mt-2" />
                        </div>

                        {{-- Prefijo de Miembro --}}
                        <div class="mt-4">
                            <x-input-label for="member_code_prefix" :value="__('Prefijo para Código de Miembro')" />
                            <x-text-input id="member_code_prefix" class="block mt-1 w-full" type="text" name="member_code_prefix" :value="old('member_code_prefix', $settings['member_code_prefix'] ?? 'GYM-')" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ej: "GYM-" o "SOCIO-". Déjalo vacío si solo quieres números.</p>
                            <x-input-error :messages="$errors->get('member_code_prefix')" class="mt-2" />
                        </div>

                        {{-- Zona Horaria --}}
                        <div class="mt-4">
                            <x-input-label for="app_timezone" :value="__('Zona Horaria de la Aplicación')" />
                            <select name="app_timezone" id="app_timezone" required class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm">
                                @php
                                    $currentTimezone = old('app_timezone', $settings['app_timezone'] ?? config('app.timezone'));
                                @endphp
                                @foreach ($timezones as $timezone)
                                    <option value="{{ $timezone }}" @selected($timezone == $currentTimezone)>
                                        {{ str_replace('_', ' ', $timezone) }}
                                    </option>
                                @endforeach
                            </select>
                             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Afecta las fechas y horas mostradas.</p>
                            <x-input-error :messages="$errors->get('app_timezone')" class="mt-2" />
                        </div>

                        {{-- ========= INICIO: CORREO DESTINO REPORTES ========= --}}
                        {{-- ESTE ES EL CAMPO QUE PROBABLEMENTE TE FALTA --}}
                        <div class="mt-4">
                            <x-input-label for="report_recipient_email" :value="__('Correo para Recibir Reportes de Caja')" />
                            <x-text-input id="report_recipient_email" class="block mt-1 w-full" type="email" name="report_recipient_email" :value="old('report_recipient_email', $settings['report_recipient_email'] ?? '')" placeholder="ejemplo@dominio.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Deja vacío si no quieres usar la función "Enviar Reporte por Correo".</p>
                            <x-input-error :messages="$errors->get('report_recipient_email')" class="mt-2" />
                        </div>
                        {{-- ========= FIN: CORREO DESTINO REPORTES ========= --}}

                    </div>
                </div>

                <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-white">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuración de Correo (Gmail)</h3>
                         <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Usa una "Contraseña de Aplicación" de Google.</p>
                         <div class="grid grid-cols-2 gap-4">
                            {{-- Correo --}}
                            <div>
                                <x-input-label for="mail_username" :value="__('Tu Correo de Gmail')" />
                                <x-text-input id="mail_username" class="block mt-1 w-full" type="email" name="mail_username" :value="old('mail_username', $settings['mail_username'] ?? '')" required />
                                <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                            </div>
                            {{-- Nombre Remitente --}}
                            <div>
                                <x-input-label for="mail_from_name" :value="__('Nombre del Remitente')" />
                                <x-text-input id="mail_from_name" class="block mt-1 w-full" type="text" name="mail_from_name" :value="old('mail_from_name', $settings['mail_from_name'] ?? 'Gimnasio')" required />
                                <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                            </div>
                         </div>
                         <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nombre que verán tus miembros.</p>
                         {{-- Contraseña App --}}
                        <div class="mt-4">
                            <x-input-label for="mail_password" :value="__('Contraseña de Aplicación (16 letras)')" />
                            <x-text-input id="mail_password" class="block mt-1 w-full" type="password" name="mail_password" :value="old('mail_password', $settings['mail_password'] ?? '')" required placeholder="xxxx yyyy zzzz wwww" />
                            <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                        </div>
                        {{-- Ocultos --}}
                        <input type="hidden" name="mail_host" value="smtp.gmail.com">
                        <input type="hidden" name="mail_port" value="465">
                        <input type="hidden" name="mail_encryption" value="ssl">
                    </div>
                </div>

                <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg mb-6">
                     <div class="p-6 text-gray-900 dark:text-white">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Imágenes</h3>

                        {{-- Logo del Gimnasio --}}
                        <div class="mt-4">
                            <x-input-label for="gym_logo" :value="__('Logo del Gimnasio (barra de navegación)')" />
                            <input id="gym_logo" class="block mt-1 w-full border-gray-300 dark:border-[#444444] rounded-md shadow-sm focus:ring-[#171A20] dark:focus:ring-white focus:border-[#171A20] dark:focus:border-white" type="file" name="gym_logo" />
                            <x-input-error :messages="$errors->get('gym_logo')" class="mt-2" />
                            @if(isset($settings['gym_logo']) && $settings['gym_logo'] && (str_starts_with($settings['gym_logo'], 'http') || Storage::disk('public')->exists($settings['gym_logo'])))
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Logo actual:</p>
                                    <img src="{{ (!empty($settings['gym_logo']) && str_starts_with($settings['gym_logo'], 'http') ? $settings['gym_logo'] : Storage::url($settings['gym_logo'])) }}" alt="Logo actual" class="h-10 w-auto object-contain bg-gray-100 dark:bg-[#1A1A1A] p-1 rounded">
                                </div>
                            @elseif(isset($settings['gym_logo']) && $settings['gym_logo'])
                                 <p class="text-xs text-red-600 mt-1">Archivo de logo no encontrado. Vuelve a subirlo.</p>
                            @endif
                        </div>

                        {{-- Imagen Principal del Dashboard --}}
                        <div class="mt-4">
                            <x-input-label for="gym_main_image" :value="__('Imagen Principal (Dashboard)')" />
                            <input id="gym_main_image" class="block mt-1 w-full border-gray-300 dark:border-[#444444] rounded-md shadow-sm focus:ring-[#171A20] dark:focus:ring-white focus:border-[#171A20] dark:focus:border-white" type="file" name="gym_main_image" />
                            <x-input-error :messages="$errors->get('gym_main_image')" class="mt-2" />
                            @if(isset($settings['gym_main_image']) && $settings['gym_main_image'] && (str_starts_with($settings['gym_main_image'], 'http') || Storage::disk('public')->exists($settings['gym_main_image'])))
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Imagen actual:</p>
                                    <img src="{{ (!empty($settings['gym_main_image']) && str_starts_with($settings['gym_main_image'], 'http') ? $settings['gym_main_image'] : Storage::url($settings['gym_main_image'])) }}" alt="Imagen actual" class="w-full h-auto rounded object-cover" style="max-height: 200px;">
                                </div>
                             @elseif(isset($settings['gym_main_image']) && $settings['gym_main_image'])
                                 <p class="text-xs text-red-600 mt-1">Archivo de imagen principal no encontrado. Vuelve a subirlo.</p>
                             @endif
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════
                     TARJETA: Control de Acceso con IA
                ════════════════════════════════════════════════ --}}
                <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-white">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Control de Acceso</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Activa el reconocimiento facial con IA para el check-in. Desactívalo si no tienes cámara disponible o prefieres solo usar el código de socio.</p>

                        <label class="flex items-center gap-4 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" id="biometrics_enabled" name="biometrics_enabled" value="1"
                                       class="sr-only peer"
                                       @if(old('biometrics_enabled', $settings['biometrics_enabled'] ?? '0') == '1') checked @endif>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                            after:bg-white dark:bg-[#000000] after:border-gray-300 dark:border-[#444444] after:border after:rounded-full
                                            after:h-5 after:w-5 after:transition-all
                                            peer-checked:bg-[#171A20] dark:bg-white text-white dark:text-[#171A20]"></div>
                            </div>
                            <div>
                                <span class="font-medium text-gray-800 dark:text-gray-200">Reconocimiento Facial con IA</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" id="biometrics-status-label">
                                    @if(($settings['biometrics_enabled'] ?? '0') == '1')
                                        <span class="text-green-600 font-semibold">Activado</span> — La cámara pedirá acceso al abrir el Check-in.
                                    @else
                                        <span class="text-gray-400">Desactivado</span> — Solo se usará el código de socio.
                                    @endif
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button>
                        {{ __('Guardar Cambios') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>







