

const station = document.getElementById('access-station');
if (station) {
    const video       = document.getElementById('access-video');
    const placeholder = document.getElementById('camera-placeholder');
    const start       = document.getElementById('camera-start');
    const stop        = document.getElementById('camera-stop');
    const status      = document.getElementById('scan-message');
    const result      = document.getElementById('access-result');
    const canvas      = document.createElement('canvas');

    const photoOverlay = document.getElementById('member-photo-overlay');
    const photoImg     = document.getElementById('member-photo-img');
    const photoName    = document.getElementById('member-photo-name');
    let photoTimer;

    let stream, timer;
    let generation  = 0;
    let lastIdentity = null;
    let lastShown    = 0;
    
    let faceMatcher = null;
    let modelsLoaded = false;

    async function loadModels() {
        if (modelsLoaded) return true;
        try {
            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            modelsLoaded = true;
            return true;
        } catch(e) {
            console.error(e);
            return false;
        }
    }

    async function loadKnownFaces() {
        try {
            const response = await fetch('/check-in/biometric/vectors');
            const members = await response.json();
            
            if (!members || members.length === 0) return null;
            
            const labeledDescriptors = [];
            for (const m of members) {
                if (m.vector && m.vector.length === 128) {
                    labeledDescriptors.push(
                        new faceapi.LabeledFaceDescriptors(
                            m.id.toString(),
                            [new Float32Array(m.vector)]
                        )
                    );
                }
            }
            if (labeledDescriptors.length === 0) return null;
            return new faceapi.FaceMatcher(labeledDescriptors, 0.45);
        } catch(e) {
            console.error('Error loading vectors:', e);
            return null;
        }
    }

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
        photoTimer = setTimeout(() => {
            photoOverlay.hidden = true;
            photoOverlay.style.display = '';
        }, 4000);
    }

    function showResult(data) {
        const allowed = data.status === 'success';
        
        result.classList.remove('bg-[#F4F4F4]', 'bg-[#E6F4EA]', 'bg-[#FCE8E6]', 'dark:bg-[#111111]', 'dark:bg-green-900/30', 'dark:bg-red-900/30');
        const title = document.getElementById('result-title');
        const message = document.getElementById('result-message');
        
        title.classList.remove('text-[#171A20]', 'text-[#137333]', 'text-[#A50E0E]', 'dark:text-white', 'dark:text-green-400', 'dark:text-red-400');
        message.classList.remove('text-[#5C5E62]', 'text-[#137333]', 'text-[#A50E0E]', 'dark:text-gray-400', 'dark:text-green-300', 'dark:text-red-300');
        
        if (allowed) {
            result.classList.add('bg-[#E6F4EA]', 'dark:bg-green-900/30');
            title.classList.add('text-[#137333]', 'dark:text-green-400');
            message.classList.add('text-[#137333]', 'dark:text-green-300');
        } else {
            result.classList.add('bg-[#FCE8E6]', 'dark:bg-red-900/30');
            title.classList.add('text-[#A50E0E]', 'dark:text-red-400');
            message.classList.add('text-[#A50E0E]', 'dark:text-red-300');
        }

        title.textContent = `¡Hola, ${data.name || 'Socio'}!`;
        message.textContent = data.message;
        document.getElementById('result-date').textContent = data.end_date ? `Vence: ${data.end_date}` : '';
        
        const photoEl = document.getElementById('result-photo');
        if (photoEl) {
            if (data.photo_path) {
                photoEl.src = data.photo_path;
                photoEl.hidden = false;
            } else {
                photoEl.hidden = true;
            }
        }
        
        showMemberPhotoInCamera(data.photo_path, data.name, allowed);
        
        const speech = new SpeechSynthesisUtterance(data.message);
        speech.lang = 'es-MX';
        speech.rate = 1.05;
        window.speechSynthesis.speak(speech);
    }

    function pauseCamera() {
        const session = ++generation;
        clearTimeout(timer);
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        if (video.srcObject) { video.pause(); video.srcObject = null; }
        placeholder.hidden = false;
        stop.hidden = true;
        status.textContent = 'Cámara en pausa';
    }

    async function scan(session) {
        if (session !== generation || !stream) return;
        let delay = 1000;
        try {
            if (!faceMatcher) {
                status.textContent = 'No hay rostros registrados.';
                return;
            }

            const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
            
            if (detection) {
                const match = faceMatcher.findBestMatch(detection.descriptor);
                
                if (match.label !== 'unknown') {
                    // Report match to backend to register access
                    const response = await fetch(station.dataset.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ member_id: match.label })
                    });
                    
                    const data = await response.json();
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
                    }
                } else {
                    status.textContent = 'Rostro no reconocido.';
                }
            } else {
                status.textContent = 'Mira de frente a la cámara';
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

    if (window._biometricsEnabled) {
        start.addEventListener('click', async () => {
            start.disabled = true;
            const session  = ++generation;
            status.textContent = 'Cargando modelos...';
            
            const modelsOk = await loadModels();
            if (!modelsOk) {
                status.textContent = 'Error al cargar IA. Revisa consola.';
                start.disabled = false;
                return;
            }
            
            status.textContent = 'Cargando rostros...';
            faceMatcher = await loadKnownFaces();

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

