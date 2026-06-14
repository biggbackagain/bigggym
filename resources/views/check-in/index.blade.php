<x-app-layout>
    {{-- Slot para el Encabezado con BOTÓN DE MODO KIOSKO --}}
    <x-slot name="header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h2 style="font-weight: 600; font-size: 1.25rem; color: #1f2937; margin: 0;">
                {{ __('Control de Acceso') }}
            </h2>
            {{-- BOTÓN DE MODO KIOSKO --}}
            <button id="kioskToggleButton" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.5rem; background-color: #0f172a; color: white; border-radius: 0.5rem; font-weight: bold; cursor: pointer; border: none; z-index: 50; transition: background-color 0.3s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <svg id="kioskIconEnter" style="height: 1.25rem; width: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <svg id="kioskIconExit" style="height: 1.25rem; width: 1.25rem; display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span id="kioskText">{{ __('ACTIVAR KIOSKO') }}</span>
            </button>
        </div>
    </x-slot>

    {{-- WRAPPER DEL DASHBOARD (TEMA CLARO MINIMALISTA) --}}
    <div id="kioskDashboard" style="padding: 2.5rem 0; background-color: #f8fafc; min-height: 100vh; transition: all 0.3s ease; font-family: ui-sans-serif, system-ui, sans-serif;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
            
            {{-- Estructura Principal: Controles vs Cámara. "align-items: stretch" garantiza que ambas columnas midan EXACTAMENTE lo mismo --}}
            <div style="display: flex; flex-wrap: wrap; gap: 2rem; align-items: stretch; min-height: 520px;">
                
                {{-- =======================================================
                     COLUMNA IZQUIERDA: Branding y Formulario 
                     ======================================================= --}}
                <div style="flex: 1 1 40%; min-width: 300px; display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    {{-- 1. SECCIÓN DE BRANDING (Ultra Minimalista) --}}
                    <div style="background-color: #ffffff; padding: 3rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-grow: 1;">
                        <h1 style="font-size: 3rem; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: 0.05em; text-transform: uppercase;">
                            {{ $gymName ?? 'WARHOUSE' }}
                        </h1>
                    </div>

                    {{-- 2. Formulario de Ingreso Manual --}}
                    <div style="background-color: #ffffff; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <div style="background-color: #f8fafc; padding: 0.5rem; border-radius: 9999px; color: #64748b; border: 1px solid #e2e8f0;">
                                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h3 style="font-size: 1.125rem; font-weight: bold; color: #0f172a; margin: 0;">Ingreso Manual</h3>
                                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Ingrese el código si la cámara falla.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('check-in.store') }}" style="margin: 0;">
                            @csrf
                            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                <div style="flex-grow: 1;">
                                    <input id="member_code" style="width: 100%; font-size: 1.125rem; font-weight: 500; background-color: #ffffff; color: #0f172a; padding: 0.75rem 1rem; border: 2px solid #cbd5e1; border-radius: 0.5rem; box-sizing: border-box; outline: none; transition: border-color 0.2s;" type="text" name="member_code" placeholder="Ej: GIM-2845..." required autocomplete="off" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'" />
                                </div>
                                <button type="submit" style="padding: 0.75rem 2rem; background-color: #0f172a; color: #ffffff; font-weight: bold; border-radius: 0.5rem; border: none; cursor: pointer; font-size: 1rem; transition: background-color 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='#0f172a'">
                                    Verificar
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- 3. ESPACIO RESERVADO PARA RESULTADOS (Evita que la pantalla salte) --}}
                    <div style="min-height: 125px; display: flex; flex-direction: column; justify-content: flex-end; flex-shrink: 0;">
                        <div id="results-panel-container" style="display: none; width: 100%;">
                            {{-- Contenido inyectado por JS --}}
                        </div>
                    </div>

                </div>

                {{-- =======================================================
                     COLUMNA DERECHA: Monitor de Cámara (Iguala la altura)
                     ======================================================= --}}
                <div style="flex: 1 1 50%; min-width: 300px; display: flex; flex-direction: column; position: relative; z-index: 10;">
                    
                    {{-- Contenedor de cámara que se expande para llenar todo el alto ("una sola entidad") --}}
                    <div id="cameraContainer" style="flex-grow: 1; background-color: #000000; border-radius: 1rem; overflow: hidden; position: relative; border: 4px solid #cbd5e1; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); width: 100%; transition: border-color 0.3s ease;">
                        
                        {{-- Video que cubre todo el espacio sin importar la proporción --}}
                        <video id="videoElement" style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);" autoplay playsinline></video>
                        
                        {{-- Capa de Feedback de Color Persistente --}}
                        <div id="cameraFlashOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; opacity: 0; transition: opacity 0.3s ease; z-index: 20;"></div>
                        
                        {{-- Retícula Visual Elegante --}}
                        <div id="cameraReticle" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; pointer-events: none; z-index: 10; opacity: 0.8;">
                            <div id="reticleBox" style="width: 60%; height: 65%; border: 2px dashed rgba(255,255,255,0.4); border-radius: 2rem; transition: border-color 0.3s ease;"></div>
                        </div>

                        {{-- Textos de Overlay (Éxito/Error) --}}
                        <div id="accessGrantedOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; opacity: 0; transition: opacity 0.3s ease; z-index: 30;">
                            <h2 style="font-size: 2.5rem; font-weight: 900; color: white; text-shadow: 0 4px 10px rgba(0,0,0,0.5); margin: 0; letter-spacing: 0.05em;">ACCESO</h2>
                            <p style="font-size: 1.25rem; font-weight: bold; color: white; text-shadow: 0 4px 10px rgba(0,0,0,0.5); margin: 0;">PERMITIDO</p>
                        </div>
                        <div id="accessDeniedOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; opacity: 0; transition: opacity 0.3s ease; z-index: 30;">
                            <h2 style="font-size: 2.5rem; font-weight: 900; color: white; text-shadow: 0 4px 10px rgba(0,0,0,0.5); margin: 0; letter-spacing: 0.05em;">ACCESO</h2>
                            <p style="font-size: 1.25rem; font-weight: bold; color: white; text-shadow: 0 4px 10px rgba(0,0,0,0.5); margin: 0;">DENEGADO</p>
                        </div>

                        {{-- Placeholder de Carga --}}
                        <div id="cameraPlaceholder" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-color: #f1f5f9; color: #64748b; z-index: 40; transition: opacity 0.3s ease;">
                            <span style="font-size: 0.875rem; font-weight: bold; letter-spacing: 0.1em; text-transform: uppercase;">Iniciando Hardware...</span>
                        </div>

                        {{-- Badge de Estado Inferior --}}
                        <div style="position: absolute; bottom: 1.5rem; left: 0; width: 100%; text-align: center; z-index: 50;">
                            <span id="scanStatus" style="display: inline-flex; align-items: center; background-color: #ffffff; color: #0f172a; padding: 0.5rem 1.25rem; border-radius: 9999px; font-family: ui-monospace, monospace; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <span id="statusDot" style="height: 0.5rem; width: 0.5rem; border-radius: 9999px; background-color: #3b82f6; margin-right: 0.5rem;"></span>
                                <span id="statusText">SISTEMA ACTIVO</span>
                            </span>
                        </div>

                        <canvas id="canvasElement" style="display: none;"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- LÓGICA JAVASCRIPT: Biometría y Modo Kiosko --}}
    <script>
        // Forzar carga de voces
        window.speechSynthesis.onvoiceschanged = () => { window.speechSynthesis.getVoices(); };

        function speakMessage(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                let utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-MX';
                window.speechSynthesis.speak(utterance);
            }
        }

        // --- LÓGICA DE MODO KIOSKO ---
        const kioskDashboard = document.getElementById('kioskDashboard');
        const kioskToggleButton = document.getElementById('kioskToggleButton');
        const kioskIconEnter = document.getElementById('kioskIconEnter');
        const kioskIconExit = document.getElementById('kioskIconExit');
        const kioskText = document.getElementById('kioskText');

        kioskToggleButton.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                kioskDashboard.requestFullscreen().catch(err => console.error(err));
                kioskIconEnter.style.display = 'none';
                kioskIconExit.style.display = 'block';
                kioskText.innerText = "SALIR KIOSKO";
                kioskDashboard.style.padding = '3rem 1rem';
            } else {
                document.exitFullscreen();
                kioskIconExit.style.display = 'none';
                kioskIconEnter.style.display = 'block';
                kioskText.innerText = "ACTIVAR KIOSKO";
                kioskDashboard.style.padding = '2.5rem 0';
            }
        });

        // --- FUNCIÓN DE FEEDBACK VISUAL SEGURO ---
        function triggerCameraState(type) {
            const cameraContainer = document.getElementById('cameraContainer');
            const flashOverlay = document.getElementById('cameraFlashOverlay');
            const grantedOverlay = document.getElementById('accessGrantedOverlay');
            const deniedOverlay = document.getElementById('accessDeniedOverlay');
            const reticleBox = document.getElementById('reticleBox');
            const statusBadge = document.getElementById('scanStatus');
            const statusDot = document.getElementById('statusDot');
            const statusText = document.getElementById('statusText');

            // Reset
            flashOverlay.style.opacity = '0';
            grantedOverlay.style.opacity = '0';
            deniedOverlay.style.opacity = '0';

            if (type === 'success') {
                flashOverlay.style.backgroundColor = 'rgba(16, 185, 129, 0.6)'; // Verde esmeralda intenso
                flashOverlay.style.opacity = '1';
                cameraContainer.style.borderColor = '#10b981';
                reticleBox.style.borderColor = 'rgba(255,255,255,0.8)';
                grantedOverlay.style.opacity = '1';
                
                statusBadge.style.backgroundColor = '#10b981';
                statusBadge.style.color = '#ffffff';
                statusBadge.style.borderColor = '#059669';
                statusDot.style.display = 'none';
                statusText.innerText = 'ACCESO CONFIRMADO ✓';

            } else if (type === 'error') {
                flashOverlay.style.backgroundColor = 'rgba(239, 68, 68, 0.7)'; // Rojo intenso
                flashOverlay.style.opacity = '1';
                cameraContainer.style.borderColor = '#ef4444';
                reticleBox.style.borderColor = 'rgba(255,255,255,0.8)';
                deniedOverlay.style.opacity = '1';

                statusBadge.style.backgroundColor = '#ef4444';
                statusBadge.style.color = '#ffffff';
                statusBadge.style.borderColor = '#b91c1c';
                statusDot.style.display = 'none';
                statusText.innerText = 'ACCESO DENEGADO ✕';

            } else {
                cameraContainer.style.borderColor = '#cbd5e1';
                reticleBox.style.borderColor = 'rgba(255,255,255,0.4)';
                
                statusBadge.style.backgroundColor = '#ffffff';
                statusBadge.style.color = '#0f172a';
                statusBadge.style.borderColor = '#e2e8f0';
                statusDot.style.display = 'inline-block';
                statusDot.style.backgroundColor = '#3b82f6';
                statusText.innerText = 'SISTEMA ACTIVO';
            }
        }

        // --- LÓGICA PRINCIPAL ---
        document.addEventListener('DOMContentLoaded', async function () {
            const video = document.getElementById('videoElement');
            const canvas = document.getElementById('canvasElement');
            const placeholder = document.getElementById('cameraPlaceholder');
            const resultsPanel = document.getElementById('results-panel-container');
            const statusText = document.getElementById('statusText');
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let isProcessing = false;
            let lastMemberMessage = null;
            let resetTimer = null;

            // Encender cámara
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                placeholder.style.opacity = '0';
                setTimeout(()=> placeholder.style.display = 'none', 300);
                triggerCameraState('reset');
            } catch (err) {
                statusText.innerText = 'ERROR DE CÁMARA';
                document.getElementById('statusDot').style.backgroundColor = '#ef4444';
                return;
            }

            // Bucle Biométrico
            setInterval(async () => {
                if (isProcessing) return;
                isProcessing = true;

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const base64Image = canvas.toDataURL('image/jpeg', 0.5);

                try {
                    const response = await fetch('/check-in/biometric', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ image: base64Image })
                    });

                    const data = await response.json();

                    if (data.status && data.status !== 'waiting') {
                        
                        if (lastMemberMessage === data.message) {
                            isProcessing = false;
                            return;
                        }
                        lastMemberMessage = data.message;

                        if (resetTimer) clearTimeout(resetTimer);
                        resultsPanel.innerHTML = '';
                        resultsPanel.style.display = 'block';
                        resultsPanel.style.animation = 'fadeIn 0.3s ease-out forwards';
                        
                        let cleanName = data.message.replace('Acceso Permitido: ', '').replace('Acceso Denegado: ', '').replace('. Membresía Vencida.', '').trim();
                        let gymName = data.gym_name || 'el gimnasio';

                        if (data.status === 'success') {
                            triggerCameraState('success');
                            speakMessage(`Bienvenido a ${gymName}, ${cleanName}.`);

                            // Tarjeta de Éxito (Light Theme Premium)
                            resultsPanel.innerHTML = `
                                <div style="background-color: #ffffff; border-left: 6px solid #10b981; border-radius: 0.75rem; padding: 1.25rem; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1.25rem;">
                                    <div style="flex-shrink: 0; position: relative;">
                                        <img src="${data.photo_path ? data.photo_path : '/images/default-avatar.png'}" alt="Socio" style="height: 4.5rem; width: 4.5rem; border-radius: 9999px; object-fit: cover; border: 2px solid #e2e8f0;">
                                        <div style="position: absolute; bottom: 0; right: 0; background-color: #10b981; border-radius: 9999px; padding: 0.25rem; color: white; border: 2px solid #ffffff;">
                                            <svg style="height: 1rem; width: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="color: #059669; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Acceso Permitido</div>
                                        <h4 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">${cleanName}</h4>
                                        <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0; font-weight: 500;">Vigencia hasta: <span style="color: #0f172a; font-weight: 700;">${data.end_date}</span></p>
                                    </div>
                                </div>
                            `;
                        } else {
                            triggerCameraState('error');
                            speakMessage(`${cleanName}, acceso denegado.`);

                            // Tarjeta de Error
                            resultsPanel.innerHTML = `
                                <div style="background-color: #ffffff; border-left: 6px solid #ef4444; border-radius: 0.75rem; padding: 1.25rem; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1.25rem;">
                                    <div style="flex-shrink: 0; position: relative;">
                                        <img src="${data.photo_path ? data.photo_path : '/images/default-avatar.png'}" alt="Socio" style="height: 4.5rem; width: 4.5rem; border-radius: 9999px; object-fit: cover; border: 2px solid #e2e8f0; opacity: 0.7;">
                                        <div style="position: absolute; bottom: 0; right: 0; background-color: #ef4444; border-radius: 9999px; padding: 0.25rem; color: white; border: 2px solid #ffffff;">
                                            <svg style="height: 1rem; width: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="color: #dc2626; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Acceso Denegado</div>
                                        <h4 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">${cleanName}</h4>
                                        <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0; font-weight: 500;">Vencida el: <span style="color: #0f172a; font-weight: 700;">${data.end_date || 'N/A'}</span></p>
                                    </div>
                                </div>
                            `;
                        }

                        // Temporizador de Limpieza
                        resetTimer = setTimeout(() => {
                            resultsPanel.style.display = 'none';
                            resultsPanel.innerHTML = '';
                            triggerCameraState('reset');
                            lastMemberMessage = null; 
                        }, 5000);
                    }
                } catch (err) {
                    console.error('Error biométrico', err);
                }

                setTimeout(() => { isProcessing = false; }, 500);

            }, 1500);
        });
    </script>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>