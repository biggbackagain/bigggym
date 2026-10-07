<template>
  <div class="mt-6 border border-gray-200 p-6 rounded-lg bg-white shadow-sm">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Registro Biométrico (IA Integrada)</h3>
    <p class="text-sm text-gray-500 mb-4">
      Captura el rostro del socio para permitirle el acceso automatizado. Asegúrate de que solo haya una persona en el cuadro y esté bien iluminada.
    </p>

    <div class="relative bg-gray-900 rounded-lg overflow-hidden w-full max-w-md mx-auto aspect-video flex items-center justify-center shadow-inner">
      <video ref="video" class="w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline v-show="isCameraOn"></video>
      <div v-if="!isCameraOn" class="text-gray-400 flex flex-col items-center">
        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <span>Cámara apagada</span>
      </div>
    </div>

    <div class="mt-6 flex justify-center gap-4">
      <button type="button" @click="startCamera" v-if="!isCameraOn" :disabled="isLoadingModels" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md transition duration-150 disabled:opacity-50">
        {{ isLoadingModels ? 'Cargando IA...' : 'Encender Cámara' }}
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
import * as faceapi from '@vladmandic/face-api';

const emit = defineEmits(['vector-extracted']);

const video = ref(null);
const canvas = ref(null);
const isCameraOn = ref(false);
const isProcessing = ref(false);
const isLoadingModels = ref(false);
const modelsLoaded = ref(false);
const message = ref('');
const isSuccess = ref(false);

let stream = null;

const loadModels = async () => {
    if (modelsLoaded.value) return;
    isLoadingModels.value = true;
    message.value = 'Cargando modelos de Inteligencia Artificial...';
    try {
        await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        modelsLoaded.value = true;
        message.value = '';
    } catch (e) {
        message.value = 'Error al cargar los modelos de IA.';
        console.error(e);
    }
    isLoadingModels.value = false;
};

const startCamera = async () => {
    await loadModels();
    if (!modelsLoaded.value) return;
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

    try {
        const detection = await faceapi.detectSingleFace(video.value).withFaceLandmarks().withFaceDescriptor();
        
        if (detection) {
            message.value = '¡Rostro escaneado y vectorizado con éxito!';
            isSuccess.value = true;
            stopCamera(); 
            // array from Float32Array
            const vectorArray = Array.from(detection.descriptor);
            emit('vector-extracted', JSON.stringify(vectorArray));
        } else {
            message.value = 'No se detectó un rostro claro. Mira fijamente a la cámara e ilumina tu rostro.';
            isSuccess.value = false;
        }
    } catch (error) {
        message.value = 'Error al procesar la imagen.';
        console.error(error);
        isSuccess.value = false;
    } finally {
        isProcessing.value = false;
    }
};

onBeforeUnmount(() => {
    stopCamera();
});
</script>