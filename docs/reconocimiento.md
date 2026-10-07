# Reconocimiento y acceso

La instalación funciona sin Docker. Consulta [instalación sin Docker](instalacion-sin-docker.md) para configurar el servicio local o remoto con `BIOMETRICS_ENABLED`, `BIOMETRICS_URL` y `BIOMETRICS_TOKEN`.

La pantalla permite código manual, activación/pausa de cámara y modo kiosco. Las fallas del servicio se distinguen del rechazo por membresía. La cámara se apaga al salir o cambiar de pestaña y no se superponen capturas.

El registro acepta vectores de 128 valores numéricos y los oculta al serializar el modelo Member. El acceso exige que la fecha actual esté entre el inicio y fin de alguna suscripción, incluso si hay otra suscripción futura.

El motor rechaza múltiples caras, caras pequeñas y coincidencias ambiguas. El umbral de distancia es 0.45 y se exige una separación mínima de 0.05 respecto a otra identidad. Estos valores requieren validación con la cámara e iluminación reales. No hay detección de vida.

Pruebas PHP: `php artisan test --filter=CheckInTest`. Usan SQLite en memoria. Pruebas Python: `python -m unittest discover -s ai-service -p test_main.py -v`. Instala `httpx` además de las dependencias del servicio para el cliente de pruebas. La detección y las distancias del modelo se sustituyen por respuestas controladas; se comprueban decisiones y autenticación, no precisión biométrica real.
