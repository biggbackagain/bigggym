const station = document.getElementById('access-station');
if (station) {
    const video       = document.getElementById('access-video');
    const placeholder = document.getElementById('camera-placeholder');
    const start       = document.getElementById('camera-start');
    const stop        = document.getElementById('camera-stop');
    const status      = document.getElementById('scan-message');
    const result      = document.getElementById('access-result');
    const canvas      = document.createElement('canvas');

    // Overlay de foto del socio en el panel de cámara
    const photoOverlay = document.getElementById('member-photo-overlay');
    const photoImg     = document.getElementById('member-photo-img');
    const photoName    = document.getElementById('member-photo-name');
    let photoTimer;

    let stream, timer, controller;
    let generation  = 0;
    let lastIdentity = null;
    let lastShown    = 0;

    // ─── Muestra la foto del socio en el panel de cámara ──────────────────
    function showMemberPhotoInCamera(photoUrl, name, allowed) {
        if (!photoOverlay || !photoUrl) return;
        clearTimeout(photoTimer);
        photoImg.src = photoUrl;
        photoImg.style.borderColor = allowed ? '#22c55e' : '#ef4444';
        photoImg.style.boxShadow   = allowed
            ? '0 0 30px rgba(34,197,94,.55)'
            : '0 0 30px rgba(239,68,68,.55)';
        photoName.textContent = name || '';
        photoOverlay.hidden = false;
        photoOverlay.style.display = 'flex';
        // Ocultar después de 4 s (si la cámara está activa volverá a escanear)
        photoTimer = setTimeout(() => {
            photoOverlay.hidden = true;
            photoOverlay.style.display = '';
        }, 4000);
    }

    // ─── Muestra el resultado en el panel lateral ──────────────────────────
    function showResult(data) {
        const allowed = data.status === 'success';
        
        // Remove previous color states
        result.classList.remove('bg-[#F4F4F4]', 'bg-[#E6F4EA]', 'bg-[#FCE8E6]', 'dark:bg-[#111111]', 'dark:bg-green-900/30', 'dark:bg-red-900/30');
        const title = document.getElementById('result-title');
        const message = document.getElementById('result-message');
        
        title.classList.remove('text-[#171A20]', 'text-[#137333]', 'text-[#A50E0E]', 'dark:text-white', 'dark:text-green-400', 'dark:text-red-400');
        message.classList.remove('text-[#5C5E62]', 'text-[#137333]', 'text-[#A50E0E]', 'dark:text-gray-400', 'dark:text-green-400', 'dark:text-red-400');

        // Add new color states
        if (allowed) {
            result.classList.add('bg-[#E6F4EA]', 'dark:bg-green-900/30');
            title.classList.add('text-[#137333]', 'dark:text-green-400');
            message.classList.add('text-[#137333]', 'dark:text-green-400');
        } else {
            result.classList.add('bg-[#FCE8E6]', 'dark:bg-red-900/30');
            title.classList.add('text-[#A50E0E]', 'dark:text-red-400');
            message.classList.add('text-[#A50E0E]', 'dark:text-red-400');
        }

        title.textContent = (allowed ? 'Acceso permitido' : 'Acceso denegado') + (data.name ? ` · ${data.name}` : '');
        message.textContent = data.message;
        document.getElementById('result-date').textContent = data.end_date ? `Fin de membresía: ${data.end_date}` : '';

        // Foto en el panel lateral (resultado)
        let photoEl = document.getElementById('result-photo');
        if (data.photo_path) {
            if (!photoEl) {
                photoEl = document.createElement('img');
                photoEl.id  = 'result-photo';
                photoEl.alt = 'Foto del socio';
                photoEl.className = 'w-16 h-16 rounded-[4px] object-cover shrink-0';
                result.insertBefore(photoEl, result.firstChild);
            }
            photoEl.src = data.photo_path;
            photoEl.classList.remove('hidden');
        } else {
            if (photoEl) {
                photoEl.classList.add('hidden');
                photoEl.src = '';
            }
        }

        // Foto en el panel de cámara (overlay)
        if (data.photo_path) {
            showMemberPhotoInCamera(data.photo_path, data.name, allowed);
        }

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const speech = new SpeechSynthesisUtterance(
                allowed ? `Bienvenido, ${data.name || ''}.` : 'Consulta tu acceso en recepción.'
            );
            speech.lang = 'es-MX';
            window.speechSynthesis.speak(speech);
        }
    }

    // Mostrar resultado inicial (check-in por código)
    const initial = JSON.parse(document.getElementById('initial-access-result').textContent);
    if (initial) showResult(initial);

    // ─── Control de cámara ─────────────────────────────────────────────────
    function pauseCamera() {
        generation++;
        clearTimeout(timer);
        controller?.abort();
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        video.srcObject = null;
        placeholder.hidden = false;
        stop.hidden = true;
        start.disabled = false;
        status.textContent = 'Cámara apagada · puedes usar tu código';
    }

    async function scan(session) {
        if (!stream || session !== generation) return;
        let delay = 1500;
        try {
            if (video.readyState < 2 || !video.videoWidth) return;
            canvas.width  = Math.min(video.videoWidth, 960);
            canvas.height = Math.round(video.videoHeight * canvas.width / video.videoWidth);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            controller = new AbortController();
            const timeout = setTimeout(() => controller?.abort(), 20000);
            let response, data;
            try {
                response = await fetch(station.dataset.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ image: canvas.toDataURL('image/jpeg', 0.85) }),
                    signal: controller.signal,
                });
                data = await response.json();
            } finally { clearTimeout(timeout); }

            if (session !== generation) return;
            if ([401, 403, 419].includes(response.status)) {
                pauseCamera();
                status.textContent = 'Sesión finalizada. Recarga la página para continuar.';
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Servicio no disponible');

            if (['success', 'error'].includes(data.status)) {
                const identity = `${data.member_id}:${data.status}`;
                if (identity !== lastIdentity || Date.now() - lastShown > 15000) {
                    showResult(data);
                    lastIdentity = identity;
                    lastShown    = Date.now();
                }
                status.textContent = 'Verificación completada';
                delay = 3500;
            } else {
                status.textContent = data.message || 'Mira de frente a la cámara';
            }
        } catch (error) {
            if (session === generation)
                status.textContent = 'Reconocimiento no disponible. Puedes usar tu código.';
            delay = 5000;
        } finally {
            if (stream && session === generation)
                timer = setTimeout(() => scan(session), delay);
        }
    }

    // Solo conectar eventos de cámara si biometría está habilitada
    if (window._biometricsEnabled) {
        start.addEventListener('click', async () => {
            start.disabled = true;
            const session  = ++generation;
            status.textContent = 'Conectando cámara…';
            try {
                if (!navigator.mediaDevices?.getUserMedia) throw new Error('Camera unavailable');
                const camera = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 960 }, height: { ideal: 720 } },
                    audio: false,
                });
                if (session !== generation) { camera.getTracks().forEach(t => t.stop()); return; }
                stream = camera;
                video.srcObject = stream;
                await video.play();
                if (session !== generation) return;
                placeholder.hidden = true;
                stop.hidden = false;
                status.textContent = 'Mira de frente a la cámara';
                scan(session);
            } catch (error) {
                if (session !== generation) return;
                pauseCamera();
                status.textContent = error.name === 'NotAllowedError'
                    ? 'Permite el uso de la cámara en tu navegador y reintenta.'
                    : 'No se pudo iniciar la cámara. Revisa la conexión y usa HTTPS o localhost.';
            } finally {
                if (session === generation) start.disabled = false;
            }
        });

        stop.addEventListener('click', pauseCamera);
        window.addEventListener('pagehide', pauseCamera);
        document.addEventListener('visibilitychange', () => { if (document.hidden) pauseCamera(); });
    }

    // Pantalla completa
    const kiosk = document.getElementById('kiosk-toggle');
    kiosk.hidden = !document.fullscreenEnabled;
    kiosk.addEventListener('click', async () => {
        try {
            if (document.fullscreenElement) await document.exitFullscreen();
            else await station.requestFullscreen();
        } catch { status.textContent = 'El navegador no permite la pantalla completa.'; }
    });
    document.addEventListener('fullscreenchange', () => {
        kiosk.textContent = document.fullscreenElement ? 'Salir de pantalla completa' : 'Pantalla completa';
    });
}

