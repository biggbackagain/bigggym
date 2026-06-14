<template>
  <div class="mt-6 border border-gray-200 p-6 rounded-lg bg-white shadow-sm">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Registro Biométrico (Opcional)</h3>
    <p class="text-sm text-gray-500 mb-4">
      Captura el rostro del socio para permitirle el acceso automatizado. Asegúrate de que solo haya una persona en el cuadro.
    </p>

    <div class="relative bg-gray-900 rounded-lg overflow-hidden w-full max-w-md mx-auto aspect-video flex items-center justify-center shadow-inner">
      <video ref="video" class="w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline v-show="isCameraOn"></video>
      <div v-if="!isCameraOn" class="text-gray-400 flex flex-col items-center">
        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <span>Cámara apagada</span>
      </div>
    </div>

    <div class="mt-6 flex justify-center gap-4">
      <button type="button" @click="startCamera" v-if="!isCameraOn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md transition duration-150">
        Encender Cámara
      </button>
      
      <button type="button" @click="takePhoto" v-if="isCameraOn" :disabled="isProcessing" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md transition duration-150 disabled:opacity-50">
        {{ isProcessing ? 'Analizando Rostro...' : 'Capturar Rostro' }}
      </button>
      
      <button type="button" @click="stopCamera" v-if="isCameraOn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-md transition duration-150">
        Apagar
      </button>
    </div>

    <div v-if="message" class="mt-4 p-3 rounded-md text-sm font-medium text-center" :class="isSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
      {{ message }}
    </div>

    <canvas ref="canvas" class="hidden"></canvas>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Emite el vector de 128 números al componente padre (el formulario)
const emit = defineEmits(['vector-extracted']);

const video = ref(null);
const canvas = ref(null);
const isCameraOn = ref(false);
const isProcessing = ref(false);
const message = ref('');
const isSuccess = ref(false);

let stream = null;

const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.value.srcObject = stream;
        isCameraOn.value = true;
        message.value = '';
    } catch (error) {
        message.value = 'Error al acceder a la cámara. Verifica los permisos de tu navegador.';
        isSuccess.value = false;
    }
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    isCameraOn.value = false;
};

const takePhoto = async () => {
    isProcessing.value = true;
    message.value = 'Extrayendo vector biométrico...';

    const context = canvas.value.getContext('2d');
    canvas.value.width = video.value.videoWidth;
    canvas.value.height = video.value.videoHeight;
    // Capturamos la imagen en el canvas
    context.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);

    // Convertimos la imagen a Base64 ligero (formato JPEG al 80% de calidad)
    const base64Image = canvas.value.toDataURL('image/jpeg', 0.8);

    try {
        // Hacemos la petición a nuestra API de Laravel
        const response = await axios.post('/api/biometrics/extract', {
            image: base64Image
        });

        if (response.data.success) {
            message.value = '¡Rostro escaneado y vectorizado con éxito!';
            isSuccess.value = true;
            // Apagamos la cámara porque ya tenemos lo que queríamos
            stopCamera(); 
            // Le pasamos el vector al formulario principal de Vue
            emit('vector-extracted', JSON.stringify(response.data.vector));
        } else {
            message.value = response.data.message;
            isSuccess.value = false;
        }
    } catch (error) {
        message.value = error.response?.data?.message || 'Error de conexión con el motor de IA.';
        isSuccess.value = false;
    } finally {
        isProcessing.value = false;
    }
};

// Medida de seguridad: apagar la cámara si la recepcionista cambia de página web
onBeforeUnmount(() => {
    stopCamera();
});
</script>