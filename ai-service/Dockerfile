FROM python:3.11-slim

WORKDIR /app

# 1. Instalamos los compiladores nativos de C++ que necesita el M2 para procesar biometría
RUN apt-get update && apt-get install -y \
    build-essential \
    cmake \
    python3-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalamos la red neuronal y el framework web
RUN pip install --no-cache-dir fastapi uvicorn face_recognition python-multipart

# 3. Le decimos cómo encender de forma segura
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]