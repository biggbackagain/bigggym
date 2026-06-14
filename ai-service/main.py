from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List
import face_recognition
import numpy as np
import base64
import io


app = FastAPI(title="BiggGym AI - Motor Biométrico")

# Estructuras de datos que esperamos de Laravel
class ImagePayload(BaseModel):
    image_base64: str

class FaceRecord(BaseModel):
    id: int
    vector: List[float]

class RecognizePayload(BaseModel):
    image_base64: str
    known_faces: List[FaceRecord]

@app.post("/api/extract-vector")
async def extract_vector(payload: ImagePayload):
    try:
        base64_data = payload.image_base64
        if "," in base64_data:
            base64_data = base64_data.split(",")[1]

        image_bytes = base64.b64decode(base64_data)
        image = face_recognition.load_image_file(io.BytesIO(image_bytes))
        face_locations = face_recognition.face_locations(image)

        if len(face_locations) == 0:
            return {"success": False, "message": "No se detectó ningún rostro."}
        if len(face_locations) > 1:
            return {"success": False, "message": "Hay más de una persona. Toma la foto individualmente."}

        face_encoding = face_recognition.face_encodings(image, known_face_locations=face_locations)[0]
        return {"success": True, "vector": face_encoding.tolist()}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/recognize")
async def recognize(payload: RecognizePayload):
    try:
        base64_data = payload.image_base64
        if "," in base64_data:
            base64_data = base64_data.split(",")[1]

        image_bytes = base64.b64decode(base64_data)
        image = face_recognition.load_image_file(io.BytesIO(image_bytes))
        
        # Modo rápido (modelo HOG) para check-in en tiempo real
        face_locations = face_recognition.face_locations(image)

        if len(face_locations) == 0:
            return {"success": True, "match": False, "message": "Nadie en cámara"}

        # Solo evaluamos a la primera persona que vea la cámara
        unknown_encoding = face_recognition.face_encodings(image, known_face_locations=[face_locations[0]])[0]

        if not payload.known_faces:
            return {"success": True, "match": False, "message": "BD vacía"}

        # Convertimos la BD a matrices matemáticas
        known_encodings = [np.array(face.vector) for face in payload.known_faces]
        known_ids = [face.id for face in payload.known_faces]

        # Calculamos la distancia Euclidiana
        face_distances = face_recognition.face_distance(known_encodings, unknown_encoding)
        best_match_index = np.argmin(face_distances)

        # 0.45 es el "sweet spot" para gimnasios (0.6 da falsos positivos, 0.3 es muy estricto)
        if face_distances[best_match_index] <= 0.45:
            return {
                "success": True,
                "match": True,
                "member_id": known_ids[best_match_index]
            }
        else:
            return {"success": True, "match": False, "message": "Rostro no registrado"}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    
app.post("/api/speak")
async def speak(payload: dict):
    text = payload.get("text", "Bienvenido")
    # Generar audio con gTTS (Acento español)
    tts = gTTS(text=text, lang='es-MX')
    
    # Guardar en memoria
    audio_stream = io.BytesIO()
    tts.write_to_fp(audio_stream)
    audio_stream.seek(0)
    
    return StreamingResponse(audio_stream, media_type="audio/mpeg")