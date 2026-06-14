<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Miembro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
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
                            <input id="profile_photo" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="file" name="profile_photo" />
                            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="is_student" class="inline-flex items-center">
                                <input id="is_student" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_student" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('¿Es estudiante? (Aplica descuento)') }}</span>
                            </label>
                        </div>

                        <hr class="my-8 border-gray-200">

                        {{-- 🟢 MÓDULO BIOMÉTRICO (INICIO) 🟢 --}}
                        <div class="mb-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Registro Biométrico (Opcional)</h3>
                            <p class="text-sm text-gray-500 mb-4">Captura el rostro del socio para el control de acceso automatizado. Asegúrate de que mire fijamente a la cámara.</p>

                            <div class="relative bg-black rounded-lg overflow-hidden w-full max-w-md mx-auto flex items-center justify-center shadow-inner" style="height: 250px;">
                                <video id="videoElement" class="w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline style="display: none;"></video>
                                <div id="cameraPlaceholder" class="text-gray-400 flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Cámara apagada</span>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-center gap-4">
                                <button type="button" id="btnStart" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md transition">
                                    Encender Cámara
                                </button>
                                <button type="button" id="btnCapture" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md transition" style="display: none;">
                                    Tomar Foto y Extraer Vector
                                </button>
                            </div>

                            <p id="statusMessage" class="mt-4 text-center text-sm font-medium"></p>

                            <input type="hidden" name="face_vector" id="face_vector_input">
                            <canvas id="canvasElement" style="display: none;"></canvas>
                        </div>
                        {{-- 🟢 MÓDULO BIOMÉTRICO (FIN) 🟢 --}}

                        <hr class="my-6 border-gray-200">

                        {{-- SECCIÓN DE MEMBRESÍA Y PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="membership_type_id" :value="__('Asignar Membresía (Opcional)')" />
                            <select name="membership_type_id" id="membership_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- No asignar membresía ahora --</option>
                                @foreach ($membershipTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FORMA DE PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="payment_method" :value="__('Forma de Pago')" />
                            <select name="payment_method" id="payment_method" onchange="toggleReferenceField()" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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

        // 2. Lógica del motor Biométrico
        document.addEventListener('DOMContentLoaded', function () {
            const video = document.getElementById('videoElement');
            const canvas = document.getElementById('canvasElement');
            const btnStart = document.getElementById('btnStart');
            const btnCapture = document.getElementById('btnCapture');
            const placeholder = document.getElementById('cameraPlaceholder');
            const statusMessage = document.getElementById('statusMessage');
            const faceVectorInput = document.getElementById('face_vector_input');

            let stream = null;

            // Encender cámara
            btnStart.addEventListener('click', async () => {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = stream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    btnStart.style.display = 'none';
                    btnCapture.style.display = 'block';
                    statusMessage.textContent = '';
                } catch (err) {
                    statusMessage.textContent = 'Error: No se pudo acceder a la cámara.';
                    statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                }
            });

            // Tomar foto y extraer matemáticas
            btnCapture.addEventListener('click', async () => {
                statusMessage.textContent = 'Analizando rostro con Inteligencia Artificial...';
                statusMessage.className = 'mt-4 text-center text-sm font-medium text-blue-600';
                btnCapture.disabled = true;
                btnCapture.classList.add('opacity-50');

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const base64Image = canvas.toDataURL('image/jpeg', 0.8);

                try {
                    const response = await fetch('/api/biometrics/extract', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token nativo de Blade para seguridad
                        },
                        body: JSON.stringify({ image: base64Image })
                    });

                    const data = await response.json();

                    if (data.success) {
                        statusMessage.textContent = '¡Rostro escaneado y vectorizado con éxito!';
                        statusMessage.className = 'mt-4 text-center text-sm font-medium text-green-600';

                        // Apagar cámara
                        stream.getTracks().forEach(track => track.stop());
                        video.style.display = 'none';
                        btnCapture.style.display = 'none';
                        placeholder.style.display = 'flex';
                        placeholder.innerHTML = '<span class="text-green-500 font-bold">Biometría Registrada ✔</span>';

                        // Guardar en el input oculto
                        faceVectorInput.value = JSON.stringify(data.vector);
                    } else {
                        statusMessage.textContent = data.message;
                        statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                        btnCapture.disabled = false;
                        btnCapture.classList.remove('opacity-50');
                    }
                } catch (err) {
                    statusMessage.textContent = 'Error de conexión con el microservicio de IA.';
                    statusMessage.className = 'mt-4 text-center text-sm font-medium text-red-600';
                    btnCapture.disabled = false;
                    btnCapture.classList.remove('opacity-50');
                }
            });
        });
    </script>
</x-app-layout>