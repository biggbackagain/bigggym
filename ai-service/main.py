"""
BiggGym AI - Motor Biométrico
Motor de reconocimiento facial usando facenet-pytorch (sin compilación C++).

Embeddings: 512 dimensiones (InceptionResnetV1 preentrenado en VGGFace2).
Detección:  MTCNN multi-scale.
Similitud:  distancia coseno.
"""
import base64
import binascii
import io
import logging
import os
import secrets
from typing import List

import numpy as np
import torch
from fastapi import Depends, FastAPI, HTTPException, Request
from PIL import Image, UnidentifiedImageError
from pydantic import BaseModel, Field, field_validator

logger = logging.getLogger(__name__)

MAX_IMAGE_BYTES = 4_500_000
EMBEDDING_SIZE  = 512
MATCH_THRESHOLD = 0.40   # distancia coseno; < 0.40 = mismo rostro
MIN_MATCH_MARGIN = 0.05  # margen mínimo sobre el segundo candidato

# ──────────────────────────────────────────────
# Carga lazy del modelo (una sola vez, al primer
# request). Evita retrasar el arranque.
# ──────────────────────────────────────────────
_mtcnn   = None
_resnet  = None

def get_models():
    global _mtcnn, _resnet
    if _mtcnn is None:
        from facenet_pytorch import MTCNN, InceptionResnetV1
        _mtcnn  = MTCNN(
            image_size=160, margin=20, keep_all=False,
            min_face_size=60, device=torch.device('cpu'),
            post_process=True,
        )
        _resnet = InceptionResnetV1(pretrained='vggface2').eval()
        logger.info('Modelos biométricos cargados.')
    return _mtcnn, _resnet


# ──────────────────────────────────────────────
# Seguridad
# ──────────────────────────────────────────────
def authorize(request: Request):
    token    = os.environ.get('BIOMETRICS_TOKEN', '')
    if len(token) < 32:
        raise HTTPException(status_code=503, detail='Servicio sin configurar.')
    provided = request.headers.get('Authorization', '')
    if not secrets.compare_digest(
        provided.encode('utf-8'),
        ('Bearer ' + token).encode('utf-8'),
    ):
        raise HTTPException(status_code=401, detail='No autorizado.')


app = FastAPI(
    title='BiggGym AI - Motor Biométrico',
    dependencies=[Depends(authorize)],
    docs_url=None,
    redoc_url=None,
    openapi_url=None,
)


# ──────────────────────────────────────────────
# Modelos Pydantic
# ──────────────────────────────────────────────
class ImagePayload(BaseModel):
    image_base64: str = Field(min_length=1, max_length=6_000_000)


class FaceRecord(BaseModel):
    id: int = Field(gt=0)
    vector: List[float] = Field(min_length=EMBEDDING_SIZE, max_length=EMBEDDING_SIZE)

    @field_validator('vector')
    @classmethod
    def finite_vector(cls, value):
        if not all(np.isfinite(v) for v in value):
            raise ValueError('El vector debe contener números finitos.')
        return value


class RecognizePayload(ImagePayload):
    known_faces: List[FaceRecord]


# ──────────────────────────────────────────────
# Helpers
# ──────────────────────────────────────────────
def decode_image_pil(encoded: str) -> Image.Image:
    """Decodifica base64 → PIL Image RGB."""
    try:
        raw = base64.b64decode(encoded.split(',', 1)[-1], validate=True)
        if not raw or len(raw) > MAX_IMAGE_BYTES:
            raise ValueError('Imagen demasiado grande')
        img = Image.open(io.BytesIO(raw))
        if img.width * img.height > 16_000_000:
            raise ValueError('Dimensiones exceden el límite')
        img.thumbnail((960, 960))
        return img.convert('RGB')
    except (ValueError, binascii.Error, UnidentifiedImageError, OSError):
        raise HTTPException(status_code=422, detail='Envía una imagen válida de hasta 4.5 MB.')


def extract_embedding(pil_image: Image.Image):
    """Detecta un rostro y extrae su embedding normalizado de 512 dims."""
    mtcnn, resnet = get_models()

    face_tensor = mtcnn(pil_image)
    if face_tensor is None:
        return None, 'No se detectó un rostro. Mira a la cámara con buena luz.'

    # MTCNN devuelve un tensor (C, H, W); lo convertimos a batch (1, C, H, W)
    if face_tensor.dim() == 4:
        # keep_all=False debería devolver solo uno, pero por si acaso tomamos el primero
        face_tensor = face_tensor[0]

    with torch.no_grad():
        embedding = resnet(face_tensor.unsqueeze(0))[0].numpy()

    # Normalizar a norma unitaria para usar distancia coseno
    norm = np.linalg.norm(embedding)
    if norm < 1e-6:
        return None, 'No se pudo leer el rostro. Reintenta con buena iluminación.'
    return embedding / norm, None


def cosine_distance(a, b) -> float:
    """1 - similitud coseno. 0 = idéntico, 2 = opuesto."""
    return float(1.0 - np.dot(np.array(a), np.array(b)))


def find_match(known_faces: List[FaceRecord], embedding: np.ndarray) -> dict:
    if not known_faces:
        return {'success': True, 'match': False, 'message': 'No hay rostros registrados.'}

    by_member: dict[int, float] = {}
    for face in known_faces:
        dist = cosine_distance(embedding, face.vector)
        by_member[face.id] = min(by_member.get(face.id, float('inf')), dist)

    ranked = sorted(by_member.items(), key=lambda x: x[1])
    member_id, best = ranked[0]

    if not np.isfinite(best) or best > MATCH_THRESHOLD:
        return {'success': True, 'match': False, 'message': 'Rostro no reconocido. Reintenta o usa tu código.'}
    if len(ranked) > 1 and ranked[1][1] - best < MIN_MATCH_MARGIN:
        return {'success': True, 'match': False, 'message': 'Coincidencia poco clara. Usa tu código de socio.'}

    return {'success': True, 'match': True, 'member_id': member_id}


# ──────────────────────────────────────────────
# Endpoints
# ──────────────────────────────────────────────
@app.get('/health')
def health():
    return {'status': 'ok'}


@app.post('/api/extract-vector')
def extract_vector(payload: ImagePayload):
    """Enrollment: extrae el vector de 512 dims de la foto de un miembro."""
    image = decode_image_pil(payload.image_base64)
    try:
        embedding, message = extract_embedding(image)
        if embedding is None:
            return {'success': False, 'message': message}
        return {'success': True, 'vector': embedding.tolist()}
    except Exception:
        logger.exception('Face enrollment failed')
        raise HTTPException(status_code=500, detail='No se pudo procesar el rostro.')


@app.post('/api/recognize')
def recognize(payload: RecognizePayload):
    """Check-in: identifica el rostro contra los vectores conocidos."""
    image = decode_image_pil(payload.image_base64)
    try:
        embedding, message = extract_embedding(image)
        if embedding is None:
            return {'success': True, 'match': False, 'message': message}
        return find_match(payload.known_faces, embedding)
    except Exception:
        logger.exception('Face recognition failed')
        raise HTTPException(status_code=500, detail='No se pudo procesar el rostro.')
