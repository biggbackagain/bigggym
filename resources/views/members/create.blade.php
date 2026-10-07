<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Miembro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white">
                    
                    <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nombre Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Teléfono')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Opcional)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="profile_photo" :value="__('Foto de Perfil (Opcional)')" />
                            <input id="profile_photo" class="block mt-1 w-full border-gray-300 dark:border-[#444444] rounded-md shadow-sm" type="file" name="profile_photo" />
                            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="is_student" class="inline-flex items-center">
                                <input id="is_student" type="checkbox" class="rounded border-gray-300 dark:border-[#444444] text-[#171A20] dark:text-white underline underline-offset-4 shadow-sm focus:ring-[#171A20] dark:focus:ring-white" name="is_student" value="1">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('¿Es estudiante? (Aplica descuento)') }}</span>
                            </label>
                        </div>

                        <hr class="my-8 border-gray-200 dark:border-[#333333]">

                        {{-- 🟢 MÓDULO BIOMÉTRICO (INICIO) 🟢 --}}
                        @if(config('services.biometrics.enabled'))
                        <div class="mb-8 p-6 bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#333333] rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Registro Biometrico (IA Integrada)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Captura el rostro del socio para el control de acceso automatizado. Asegúrate de que mire fijamente a la cámara.</p>

                            <div class="relative bg-black rounded-lg overflow-hidden w-full max-w-md mx-auto flex items-center justify-center shadow-inner" style="height: 250px;">
                                <video id="videoElement" class="w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline style="display: none;"></video>
                                <div id="cameraPlaceholder" class="text-gray-400 flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Cámara apagada</span>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-center gap-4">
                                <button type="button" id="btnStart" class="bg-[#171A20] dark:bg-white text-white dark:text-[#171A20] hover:bg-[#393C41] dark:hover:bg-gray-200 text-white font-bold py-2 px-6 rounded-md transition">
                                    Encender Cámara
                                </button>
                                <button type="button" id="btnCapture" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md transition" style="display: none;">
                                    Capturar rostro
                                </button>
                            </div>

                            <p id="statusMessage" role="status" aria-live="polite" class="mt-4 text-center text-sm font-medium"></p>
                            <x-input-error :messages="$errors->get('face_vector')" class="mt-2" />

                            <input type="hidden" name="face_vector" id="face_vector_input">
                            <canvas id="canvasElement" style="display: none;"></canvas>
                        </div>
                        {{-- 🟢 MÓDULO BIOMÉTRICO (FIN) 🟢 --}}
                        @endif

                        <hr class="my-6 border-gray-200 dark:border-[#333333]">

                        {{-- SECCIÓN DE MEMBRESÍA Y PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="membership_type_id" :value="__('Asignar Membresía (Opcional)')" />
                            <select name="membership_type_id" id="membership_type_id" class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm">
                                <option value="">-- No asignar membresía ahora --</option>
                                @foreach ($membershipTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FORMA DE PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="payment_method" :value="__('Forma de Pago')" />
                            <select name="payment_method" id="payment_method" onchange="toggleReferenceField()" class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta (Débito/Crédito)</option>
                                <option value="Transferencia">Transferencia / SPEI</option>
                            </select>
                        </div>

                        {{-- REFERENCIA (Dinámica) --}}
                        <div id="reference_container" class="mt-4 hidden">
                            <x-input-label for="payment_reference" :value="__('Referencia / Folio / Últimos 4 dígitos')" />
                            <x-text-input id="payment_reference" name="payment_reference" type="text" class="block mt-1 w-full" placeholder="Ej: Folio 8821 o Tarjeta 4421" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button class="ms-4 py-3 px-6 bg-gray-800 text-white hover:bg-gray-700">
                                {{ __('GUARDAR MIEMBRO') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS (Pagos y Biometría) --}}
    <script>
        // 1. Lógica del selector de pagos (Original)
        function toggleReferenceField() {
            const method = document.getElementById('payment_method').value;
            const container = document.getElementById('reference_container');
            if (method === 'Tarjeta' || method === 'Transferencia') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                document.getElementById('payment_reference').value = '';
            }
        }

        // 2. Logica del motor Biometrico (IA Integrada)
        document.addEventListener('DOMContentLoaded', async function () {
            const video = document.getElementById('videoElement');
            if (!video) return;
            const canvas = document.getElementById('canvasElement');
            const btnStart = document.getElementById('btnStart');
            const btnCapture = document.getElementById('btnCapture');
            const placeholder = document.getElementById('cameraPlaceholder');
            const statusMessage = document.getElementById('statusMessage');
            const faceVectorInput = document.getElementById('face_vector_input');

            let stream = null;
            let modelsLoaded = false;
            window.addEventListener('pagehide', () => stream?.getTracks().forEach(track => track.stop()));

            // Encender camara
            btnStart.addEventListener('click', async () => {
                btnStart.disabled = true;
                statusMessage.textContent = 'Cargando modelos de IA...';
                statusMessage.className = 'mt-4 text-center text-sm font-medium text-blue-600';
                
                try {
                    if (!modelsLoaded) {
                        await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                        modelsLoaded = true;
                    }
                    
                    statusMessage.textContent = 'Conectando camara...';
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 960 }, height: { ideal: 720 } }, audio: false });
                    video.srcObject = stream;
                    await video.play();
                    faceVectorInput.value = '';
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    btnStart.style.display = 'none';
                    btnCapture.style.display = 'block';
                    statusMessage.textContent = '';
                } catch (err) {
                    stream?.getTracks().forEach(track => track.stop());
                    statusMessage.textContent = 'Error: No se pudo acceder a la camara. (' + err.message + ')';
                    statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                } finally { btnStart.disabled = false; }
            });

            // Tomar foto y extraer matematicas (Browser side!)
            btnCapture.addEventListener('click', async () => {
                if (video.readyState < 2 || !video.videoWidth) {
                    statusMessage.textContent = 'Espera a que la camara este lista.';
                    return;
                }
                statusMessage.textContent = 'Analizando rostro...';
                statusMessage.className = 'mt-4 text-center text-sm font-medium text-blue-600';
                btnCapture.disabled = true;
                btnCapture.classList.add('opacity-50');

                try {
                    const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                    
                    if (detection) {
                        statusMessage.textContent = 'Rostro capturado. Guarda al socio para completar el registro.';
                        statusMessage.className = 'mt-4 text-center text-sm font-medium text-green-600';

                        // Apagar camara
                        stream.getTracks().forEach(track => track.stop());
                        video.style.display = 'none';
                        btnCapture.style.display = 'none';
                        btnStart.style.display = 'block';
                        btnStart.textContent = 'Volver a capturar';
                        placeholder.style.display = 'flex';
                        placeholder.textContent = 'Rostro capturado OK';

                        // Guardar en el input oculto (Array de 128)
                        const vectorArray = Array.from(detection.descriptor);
                        faceVectorInput.value = JSON.stringify(vectorArray);
                    } else {
                        statusMessage.textContent = 'No se detecto un rostro claro. Mira fijamente a la camara e ilumina tu rostro.';
                        statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                    }
                } catch (err) {
                    statusMessage.textContent = 'Error al procesar la imagen con IA.';
                    statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                } finally {
                    btnCapture.disabled = false;
                    btnCapture.classList.remove('opacity-50');
                }
            });
        });
    </script>
</x-app-layout>









